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
    ?>
          <form id="wizard-form">
            <div class="notice info" data-bind="visible: mode() == 1">
            Ihre Offerteanfrage wurde erfolgreich erstellt.
            </div>
            <div class="notice error" data-bind="visible: mode() == 2">
              Bei der Offertanfrage ist ein Fehler aufgetreten. Bitte versuchen sie es erneut.
            </div>
            <div>
                <!-- Wizard -->
                <div id="wizard" class="wizard"
                     data-bind="foreach: steps, attr: {'data-current-step': currentWizardStepId() + 1, 'data-steps': steps().length}">
                  <p class="step" data-bind="text:name, click:function() {$parent.currentStep($data)}"></p>
                </div>

                <!-- Master Byline -->
                <div class="masterByline">
                  <div class="bylineItem" data-bind="visible: form.totalPrice() > 0">voraussichtlicher Preis: <span data-bind="text: readablePrice(form.totalPrice())"></span></div>
                  <div class="bylineItem" data-bind="visible: form.totalMaxPersons() > 0">Personen: <span data-bind="text: form.totalMinPersons"></span> - <span data-bind="text: form.totalMaxPersons"></span></div>
                  <div class="bylineItem" data-bind="visible: form.totalSquareMeters() > 0">Gesamtfläche: <span data-bind="text: form.totalSquareMeters"></span>m³</div>
                </div>
                <!-- Zelt -->
                <div data-bind="template: { name: 'step1-template', data: steps()[0] }"></div>
                <script type="text/html" id="step1-template">
                    <div class="wizardStepView" data-bind="visible: isVisible">
                      <h2>Meine Zelte</h2>
                      <div class="addButton" data-bind="click: openSelectTent">
                        <i class="fa fa-plus-circle"></i> Zelt hinzufügen
                      </div>
                      <ul class="listing" data-bind="foreach: wizardForm.selectedTents">
                        <li>
                          <div data-bind="template: { name: 'remove-row-template' }" />
                          <div class="infos">
                            <div class="title">
                              <span data-bind="text: minPersons"></span> - <span data-bind="text: maxPersons"></span> Personen<span data-bind="text: $root.readablePrice(price)" class="price"></span>
                            </div>
                            <div class="byline">
                              Länge: <span data-bind="text: length"></span>m | Breite: <span data-bind="text: width"></span>m | Fläche: <span data-bind="text: squareMeter"></span>m³
                            </div>
                          </div>
                        </li>
                      </ul>
                      <div data-bind="visible: wizardForm.selectedTents().length">
                        <div class="total">
                          <span>Total Zelte inkl. MwSt.</span><span class="price" data-bind="text: $root.readablePrice(wizardForm.totalTentsPrice())"></span>
                        </div>
                        <div class="info">
                        <span>Total Personen</span><span class="price" data-bind="text: $root.readablePersons(wizardForm.totalMinPersons(), wizardForm.totalMaxPersons())">400 - 800 Personen</span>
                        </div>
                        <div class="info">
                        </div>
                      </div>
                      <!-- <div data-bind="template: { name: 'navigation-controls-template' }" />       -->
                    </div>
                    <div data-bind="template: { name: 'select-tents-template', data: selectTentsSubStep }"></div>
                </script>

                <!-- Zubehör -->
                <div data-bind="template: { name: 'step2-template', data: steps()[1] }"></div>
                <script type="text/html" id="step2-template">
                    <div class="wizardStepView" data-bind="visible: isVisible">
                      <h2>Zubehör</h2>
                      <div class="addButton" data-bind="click: openSelectEquipment">
                        <i class="fa fa-plus-circle"></i> Zubehör hinzufügen
                      </div>
                      <ul class="listing" data-bind="foreach: wizardForm.selectedEquipments">
                        <li>
                          <div data-bind="template: { name: 'remove-row-template' }" />
                          <div class="infos">
                            <div class="title">
                              <span data-bind="text: title"></span><span data-bind="text: $root.readablePrice(price)" class="price">
                            </div>
                            <div class="byline">
                              <span data-bind="text: description"></span>
                            </div>
                          </div>
                        </li>
                      </ul>
                      <div data-bind="visible: wizardForm.selectedEquipments().length">
                        <div class="total">
                          <span>Total Equipment inkl. MwSt.</span><span class="price" data-bind="text: $root.readablePrice(wizardForm.totalEquipmentsPrice())"></span>
                        </div>
                        <div class="info">
                        </div>
                      </div>
                    </div>
                    <div data-bind="template: { name: 'select-equipment-template', data: selectEquipmentSubStep }"></div>
                </script>

                <!-- Kontakt -->
                <div data-bind="template: { name: 'contact-template', data: steps()[2] }"></div>
                <script type="text/html" id="contact-template">
                    <div class="wizardStepView" data-bind="visible: isVisible">
                      <h2>Kontakt</h2>
                      <div class="contactForm">
                        <label for="salutation">Anrede</label>
                        <select id="salutation" data-bind="options: availableGenders" required></select>

                        <label for="firstname">Vorname</label>
                        <input id="firstname" data-bind="value: $root.form.firstname" type="text" placeholder="Vorname">

                        <label for="lastname">Nachname</label>
                        <input id="lastname" data-bind="value: $root.form.lastname" type="text" placeholder="Nachname" required>

                        <label for="company">Firma</label>
                        <input id="company" data-bind="value: $root.form.company" type="text" placeholder="Firma">

                        <label for="street">Strasse</label>
                        <input id="street" data-bind="value: $root.form.street" type="text" placeholder="Strasse" required>

                        <label for="streetnr">Nr.</label>
                        <input id="streetnr" data-bind="value: $root.form.streetnr" type="text" placeholder="Nr." required>

                        <label for="zip">PLZ</label>
                        <input id="zip" data-bind="value: $root.form.zip" type="number" placeholder="PLZ" required>

                         <label for="location">Ort</label>
                        <input id="location" data-bind="value: $root.form.location" type="text" placeholder="Ort" required>
                        
                        <label for="email">E-Mail</label>
                        <input id="email" data-bind="value: $root.form.email" type="email" placeholder="E-Mail" required>

                        <label for="phone">Telefon</label>
                        <input id="phone" data-bind="value: $root.form.phone" type="phone" placeholder="Telefon" required>
                      </div>
                    </div>
                </script>

                <!-- Zelt hinzufügen -->
                <script type="text/html" id="select-tents-template">
                    <div class="wizardStepView wizardStepSubView" data-bind="visible: isVisible">
                      <h2>Zeltauswahl</h2>
                      <ul class="listing selectable" data-bind="foreach: parent.wizardForm.tents">
                          <li tab-index="10" data-bind="click: $parent.selectTent.bind($parent)">
                            <div class="title">
                              <span data-bind="text: minPersons"></span> - <span data-bind="text: maxPersons"></span> Personen<span data-bind="text: $root.readablePrice(price)" class="price"></span>
                            </div>
                            <div class="byline">
                              Länge: <span data-bind="text: length"></span>m | Breite: <span data-bind="text: width"></span>m | Fläche: <span data-bind="text: squareMeter"></span>m³
                            </div>
                          </li>
                      </ul>
                    </div>
                 </script>

                <!-- Zubehör hinzufügen -->
                <script type="text/html" id="select-equipment-template">
                    <div class="wizardStepView wizardStepSubView" data-bind="visible: isVisible">
                      <h2>Zubehörauswahl</h2>
                      <ul class="listing selectable" data-bind="foreach: parent.wizardForm.equipments">
                          <li data-bind="click: $parent.selectEquipment.bind($parent)">
                            <div class="title">
                              <span data-bind="text: title"></span><span data-bind="text: $root.readablePrice(price)" class="price">
                            </div>
                            <div class="byline">
                              <span data-bind="text: description"></span>
                            </div>
                          </li>
                      </ul>
                    </div>
                 </script>

                 <!-- Navigation Controls -->
                 <div class="navigationControls">
                  <button data-bind="click: previous, visible: showPrevButton">Zurück</button>
                  <input class="right highlight" type="submit" class="right" data-bind="click: send, visible: showSendButton" value="Offerte anfordern"/>
                  <button class="right highlight" data-bind="click: next, visible: showNextButton">Weiter</button>
                </div>
            </div>
          </form>
    <?php
}
