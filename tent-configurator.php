<?php

/**
 * @link              www.kultagentur-hauta.ch
 * @since             1.0.0
 * @package           Tent_Configurator
 *
 * @wordpress-plugin
 * Plugin Name:       KAH Tent Configurator
 * Plugin URI:        www.kultagentur-hauta.ch
 * Description:       Use this plugin for a tent-configuration form in your sites.
 * Version:           1.0.0
 * Author:            Elio Schmutz
 * Author URI:        www.kultagentur-hauta.ch
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

include 'tent-configurator-tents.php';
include 'tent-configurator-equipment.php';

class Model {
    public function get_tents() {
        return $GLOBALS['tent_data'];
    }
    public function get_equipment() {
        return $GLOBALS['equipment_data'];
    }
    public function get_data() {
        return array(
            'tents' => $this->get_tents(),
            'equipment' => $this->get_equipment()
            );
    }
}

function tentconfigurator_enqueue_script() {
  wp_enqueue_script(
    'tent-configurator', plugin_dir_url( __FILE__ ) . 'dist/js/all.min.js',
    array( 'jquery', 'knockoutjs'), '1.0.0', false );
}

function tentconfigurator_enqueue_styles() {
  wp_enqueue_style( 'tent-configurator', plugin_dir_url( __FILE__ ) . 'dist/css/main.css', array('dashicons'), '1.0.0', 'all' );  
}

add_action( 'wp_enqueue_scripts', 'tentconfigurator_enqueue_script' );
add_action( 'wp_enqueue_scripts', 'tentconfigurator_enqueue_styles' );

add_shortcode('tent-configurator', 'tent_configurator_creation');

function tent_configurator_creation() {
    global $mk_options;
    wp_enqueue_script('gmaps', 'https://maps.googleapis.com/maps/api/js?key='.$mk_options['google_maps_api_key'].'&libraries=places&callback=kah.tentconfigurator.initGoogleSearch', false, false, false);
    return file_get_contents( plugin_dir_url( __FILE__ ) . 'dist/form.html' );
}

add_action( 'wp_ajax_nopriv_kah_send_offer', 'kah_send_offer' );
add_action( 'wp_ajax_kah_send_offer', 'kah_send_offer' );

function kah_send_offer() {
    if ( ! json_decode($_POST['acceptTerms']) ) {
        wp_send_json_error();
    }
    $action = sanitize_text_field($_POST['action']);
    $salutation = sanitize_text_field($_POST['salutation']);
    $firstName = sanitize_text_field($_POST['firstName']);
    $lastName = sanitize_text_field($_POST['lastName']);
    $company = sanitize_text_field($_POST['company']);
    $street = sanitize_text_field($_POST['street']);
    $streetnr = sanitize_text_field($_POST['streetnr']);
    $zip = sanitize_text_field($_POST['zip']);
    $location = sanitize_text_field($_POST['location']);
    $email = sanitize_text_field($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $comment = sanitize_text_field($_POST['comment']);
    $acceptTerms = sanitize_text_field($_POST['acceptTerms']);
    $eventname = sanitize_text_field($_POST['eventname']);
    $eventdays = sanitize_text_field($_POST['eventdays']);
    $eventlocation = sanitize_text_field($_POST['eventlocation']);
    $eventlocationdistance = ceil(sanitize_text_field($_POST['eventlocationdistance']) / 1000);
    $eventstartdate = date_i18n(
        get_option( 'date_format' ),
        strtotime(sanitize_text_field($_POST['eventstartdate'])));
    $transportPrice = sanitize_text_field($_POST['transportPrice']);
    $totalTentsPrice = sanitize_text_field($_POST['totalTentsPrice']);
    $totalEquipmentPrice = sanitize_text_field($_POST['totalEquipmentPrice']);
    $totalPrice = sanitize_text_field($_POST['totalPrice']);
    $totalMinPersons = sanitize_text_field($_POST['totalMinPersons']);
    $totalMaxPersons = sanitize_text_field($_POST['totalMaxPersons']);
    $totalSquareMeters = sanitize_text_field($_POST['totalSquareMeters']);

    // Handle required fields
    if (!$salutation || !$firstName || !$lastName || !$street ||
        !$streetnr || !$zip || !$location || !$email || !$phone ||
        !$eventname || !$eventlocation) {
        wp_send_json_error();
    }

    $selectedTentsString = '';
    foreach ($_POST['selectedTents'] as $key=>$value) {
        $index = $key + 1;
        $tent_title = sanitize_text_field($value['title']);
        $tent_price = sanitize_text_field($value['price']);
        if ($tent_price == 0) {
            $tent_price = "auf Anfrage";
        } else {
            $tent_price .= " CHF";
        };
        $tent_length = sanitize_text_field($value['length']);
        $tent_width = sanitize_text_field($value['width']);
        $tent_minPersons = sanitize_text_field($value['minPersons']);
        $tent_maxPersons = sanitize_text_field($value['maxPersons']);
        $tent_squareMeters = sanitize_text_field($value['squareMeters']);
        $tent_selectedTentsString .= "
Zelt {$index}
Titel: {$tent_title}
Preis pro Tag: {$tent_price}
Dimensionen (LxB): {$tent_length}m x {$tent_width}m
Personen: {$tent_value['minPersons']} - {$tent_maxPersons}
Quadratmeter: {$tent_squareMeters}
------------
        ";
    };

    unset($value);

    $selectedEquipmentString = '';
    foreach ($_POST['selectedEquipment'] as $key=>$value) {
        $index = $key + 1;
        $equipment_title = sanitize_text_field($value['title']);
        $equipment_price = sanitize_text_field($value['price']);
        if ($equipment_price == 0) {
            $equipment_price = "auf Anfrage";
        } else {
            $equipment_price .= " CHF";
        };
        $equipment_description = sanitize_text_field($value['description']);
        $selectedEquipmentString .= "
Equipment {$index}
Titel: {$equipment_title}
Preis pro Tag: {$equipment_price}
Beschreibung: {$equipment_description}
------------
        ";
    };
    
    unset($value);

    $to_kah = get_option( 'admin_email' );
    $to_customer = sanitize_email($_POST['email']);
    $subject_kah = "Zeltanfrage von {$salutation} {$firstName} {$lastName}";
    $subject_customer = "Offertanfrage für Zeltvermietung";
    $message_customer = "Hallo {$salutation} {$lastName}

Vielen Dank für Ihre Anfrage.

Wir werden die Daten prüfen und anschliessen mit Ihnen Kontakt aufnehmen.

Freundliche Grüße

Kult-Agentur Hauta";

    $message_kah = "Hey Leute! Wir haben eine neue Zeltanfrage erhalten.

Voraussichtlicher Gesamtpreis: {$totalPrice} CHF

Kommentar: ${comment}

Kontaktdaten:
=============

Anrede: {$salutation}
Name: {$lastName}
Vorname: {$firstName}
Firma: {$company}
Strasse / Nr: {$street} {$streetnr}
PLZ / Ort: {$zip} {$location}

E-Mail: {$email}
Telefon: {$phone}

Eventdaten:
===========

Eventname: {$eventname}
Eventort: {$eventlocation}
Startdatum: {$eventstartdate}
Anzahl Tage: {$eventdays}
Distanz pro Fahrt: {$eventlocationdistance} KM

Ausgewählte Zelte:
==================
{$selectedTentsString}

Ausgewähltes Equipment:
=======================
{$selectedEquipmentString}

Unverbindlich angezeigte Preise:
================================
Voraussichtlicher Gesamtpreis: {$totalPrice} CHF
Total für {$eventdays} Tage: {$totalPrice} CHF
Zeltmiete Total pro Tag: {$totalTentsPrice} CHF
Equipmentmiete Total pro Tag: {$totalEquipmentPrice} CHF
Transportkosten hin und zurück: {$transportPrice} CHF

Sonstige Informationen
======================
Personenanzahl: von {$totalMinPersons} bis {$totalMaxPersons}
Benötigte Quadratmeter für Zelte: {$totalSquareMeters}

Weitere Schritte:
=================
- Den Kunden im Bitrix erfassen: http://bitrix24.de
- Prüfen, ob wir die Zelte am angegebenen Datum liefern können
- Kontakt mit dem Kunden aufnehmen
- Persönliche Offerte erstellen

Gesendet mit ♥ von der Kultagentur-Hauta
    ";
    $headers = array('From:max.muster@gmail.com');

    wp_mail( $to_kah, $subject_kah, $message_kah, $headers );
    wp_mail( $to_customer, $subject_customer, $message_customer, $headers );
    wp_die();
    
}

add_action( 'wp_ajax_nopriv_kah_get_data', 'kah_get_data' );
add_action( 'wp_ajax_kah_get_data', 'kah_get_data' );

function kah_get_data() {
    $model = new Model();
    wp_send_json($model->get_data());
}
