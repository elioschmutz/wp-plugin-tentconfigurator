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


add_action( 'wp_ajax_nopriv_kah_get_data', 'kah_get_data' );

function kah_get_data() {
    $model = new Model();
    wp_send_json($model->get_data());
}
