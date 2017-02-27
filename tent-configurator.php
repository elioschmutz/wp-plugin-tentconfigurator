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

class Model {
    public function get_tents() {
        return array(
            array(
                'id' => 1,
                'title' => 'Grosses Zelt',
                'price' => 15,
                'length' => 4,
                'width' => 20,
                'minPersons' => 100,
                'maxPersons' => 150
            ),
            array(
                'id' => 2,
                'title' => 'Grosses Zelt',
                'price' => 18,
                'length' => 4,
                'width' => 20,
                'minPersons' => 120,
                'maxPersons' => 160
            )
        );
    }
    public function get_equipment() {
        return array(
            array(
                'id' => 1,
                'title' => 'Lampe',
                'price' => 15,
                'description' => 'kleine Lampe für das DJ Pult',
            ),
            array(
                'id' => 2,
                'title' => 'Tisch',
                'price' => 20,
                'description' => 'Tisch für 6 Personen inkl. 2 Bänke',
            )
        );
    }
    public function get_data() {
        return array(
            'tents' => $this->get_tents(),
            'equipment' => $this->get_equipment()
            );
    }
}
function google_api() {
    echo '<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqddQmeI9i2Ymt9kgrRPfZCio4uReQyI8&libraries=places&callback=initGoogleSearch"
     async defer></script>';
}
add_action('admin_head', 'google_api');
add_action('wp_head', 'google_api');

function tentconfigurator_enqueue_script() {
  wp_enqueue_script(
    'tent-configurator', plugin_dir_url( __FILE__ ) . 'dist/js/all.min.js',
    array( 'jquery', 'knockoutjs'), '1.0.0', false );
}

function tentconfigurator_enqueue_styles() {
  wp_enqueue_style( 'tent-configurator', plugin_dir_url( __FILE__ ) . 'dist/css/main.css', array(), '1.0.0', 'all' );  
}

add_action( 'wp_enqueue_scripts', 'tentconfigurator_enqueue_script' );
add_action( 'wp_enqueue_scripts', 'tentconfigurator_enqueue_styles' );

add_shortcode('tent-configurator', 'tent_configurator_creation');

function tent_configurator_creation() {
    return file_get_contents( plugin_dir_url( __FILE__ ) . 'dist/form.html' );
}

add_action( 'wp_ajax_nopriv_kah_send_offer', 'kah_send_offer' );

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
        $selectedTentsString .= "
Zelt {$index}
Titel: {$value['title']}
Preis pro Tag: {$value['price']} CHF
Dimensionen (LxB): {$value['length']}m x {$value['width']}m
Personen: {$value['minPersons']} - {$value['maxPersons']}
Quadratmeter: {$value['squareMeter']}
------------
        ";
    };
    unset($value);

    // error_log( print_r($_POST['selectedTents'], TRUE) );
    // error_log($_POST['selectedTents'][0]['title']);
    $to = sanitize_email($_POST['email']);
    $subject = "Zeltanfrage von {$salutation} {$firstName} {$lastName}";
    $message = "Zeltanfrage von {$salutation} {$firstName} {$lastName} erhalten!

Voraussichtlicher Gesamtpreis: {$totalPrice} CHF

Kommentar vom Kunden: ${comment}

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
{$selectedTentsString}

Ausgewähltes Equipment

Unverbindliche Preise:
================================
Total für {$eventdays} Tage: ${totalPrice}
Zeltmiete pro Tag: ${totalTentsPrice}
Equipmentmiete pro Tag: ${totalEquipmentPrice}

Transportkosten hin und zurück: 

Weitere Schritte:
=================
- Den Kunden im Bitrix erfassen: http://bitrix24.de
- Prüfen, ob wir die Zelte am angegebenen Datum liefern können
- Kontakt mit dem Kunden aufnehmen
- Persönliche Offerte erstellen

Gesendet mit ♥ von der Kultagentur-Hauta
    ";
    $headers = array('From:max.muster@gmail.com');

    wp_mail( $to, $subject, $message, $headers );
    wp_die();
    
}

add_action( 'wp_ajax_nopriv_kah_get_data', 'kah_get_data' );

function kah_get_data() {
    $model = new Model();
    wp_send_json($model->get_data());
}
