var kah = kah || {};
kah.tentconfigurator = kah.tentconfigurator || {};
kah.tentconfigurator.googleAPILoaded = new Promise(function(resolve) {
  jQuery(window).on('kahGoogleAPILoaded', function() {
    resolve();
  });
});

kah.tentconfigurator.Tent = class Tent {
  constructor(id, title, price, length, width, minPersons, maxPersons) {
    this.id = id;
    this.title = title;
    this.price = price;
    this.length = length;
    this.width = width;
    this.minPersons = minPersons;
    this.maxPersons = maxPersons;
    this.squareMeter = this.length * this.width;
  }
};

kah.tentconfigurator.Equipment = class Equipment {
  constructor(id, title, price, description) {
    this.id = id;
    this.title = title;
    this.price = price; 
    this.description = description;
  }
};

kah.tentconfigurator.WizardStep = class WizardStep {
  constructor(name='', wizardForm=null) {
    this.name = name;
    this.active = ko.observable(false);
    this.activeSubStep = ko.observable();
    this.wizardForm = wizardForm;
    this.isVisible = ko.computed(function() {
      return this.active() && !this.activeSubStep();
    }, this);
  }
  openSubStep(substep) {
    substep.active(true);
    this.activeSubStep(substep);
  }
  closeSubStep() {
    this.activeSubStep().active(false);
    this.activeSubStep(null);
  }
  removeDataRow() { throw new Error('Override this function'); }
};

kah.tentconfigurator.WizardSubStep = class WizardSubStep extends kah.tentconfigurator.WizardStep {
  constructor(parent, ...args) {
    super(...args);
    this.parent = parent;
    this.isVisible = ko.computed(function() {
      return this.active() && this.parent.active();
    }, this);
  }
};

kah.tentconfigurator.WizardStepTents = class WizardStepTents extends kah.tentconfigurator.WizardStep {
  constructor(...args) {
    super(...args);
    this.selectTentsSubStep = new kah.tentconfigurator.WizardStepSelectTents(this);
    this.openSubStep(this.selectTentsSubStep);
  }
  openSelectTent() {
    this.openSubStep(this.selectTentsSubStep);
  }
  removeDataRow(tent) {
    this.wizardForm.selectedTents.remove(tent);
  }
};

kah.tentconfigurator.WizardStepEquipment = class WizardStepEquipment extends kah.tentconfigurator.WizardStep {
  constructor(...args) {
    super(...args);
    this.selectEquipmentubStep = new kah.tentconfigurator.WizardStepSelectEquipment(this);
    this.openSubStep(this.selectEquipmentubStep);
  }
  openSelectEquipment() {
    this.openSubStep(this.selectEquipmentubStep);
  }
  removeDataRow(equipment) {
    this.wizardForm.selectedEquipment.remove(equipment);
  } 
};

kah.tentconfigurator.WizardStepEvent = class WizardStepEvent extends kah.tentconfigurator.WizardStep {
};


kah.tentconfigurator.WizardStepContact = class WizardStepContact extends kah.tentconfigurator.WizardStep {
  constructor(...args) {
    super(...args);
    this.availableGenders = ko.observableArray(['Herr', 'Frau']);
  }
};

kah.tentconfigurator.WizardStepSelectTents = class WizardStepSelectTents extends kah.tentconfigurator.WizardSubStep {
  constructor(parent, ...args) {
    super(parent, ...args);
  }
  selectTent(tent) {
    this.parent.wizardForm.selectedTents.push(Object.assign(new kah.tentconfigurator.Tent(), tent));
    this.parent.closeSubStep();
  }
};

kah.tentconfigurator.WizardStepSelectEquipment = class WizardStepSelectEquipment extends kah.tentconfigurator.WizardSubStep {
  selectEquipment(equipment) {
    this.parent.wizardForm.selectedEquipment.push(Object.assign(new kah.tentconfigurator.Equipment(), equipment));
    this.parent.closeSubStep();
  }
};

kah.tentconfigurator.WizardForm = class WizardForm {
  constructor() {
    this.firstName = ko.observable();
    this.lastName = ko.observable();
    this.salutation = ko.observable();
    this.company = ko.observable();
    this.street = ko.observable();
    this.streetnr = ko.observable();
    this.zip = ko.observable();
    this.location = ko.observable();
    this.email = ko.observable();
    this.phone = ko.observable();
    this.comment = ko.observable();
    this.acceptTerms = ko.observable(false);

    this.eventname = ko.observable();
    this.eventdays = ko.observable(1);
    this.eventlocation = ko.observable();
    this.eventlocationdistance = ko.observable(0);
    this.eventstartdate = ko.observable();

    this.tents = ko.observableArray();
    this.equipment = ko.observableArray();
    this.selectedTents = ko.observableArray();
    this.selectedEquipment = ko.observableArray();
    this.transportPrice = ko.computed(function() {
      if ( this.eventlocationdistance() <= 10000 ) {
        return 0;
      } else {
        // 50.- each 30km * 2 (for twice the distance)
        return Math.ceil(this.eventlocationdistance() / 1000 / 30) * 50 * 2;
      }
    }, this);
    this.totalTentsPrice = ko.computed(function() {
      return this._count_objs_attribute(this.selectedTents(), 'price');
    }, this);
    this.totalEquipmentPrice = ko.computed(function() {
      return this._count_objs_attribute(this.selectedEquipment(), 'price');
    }, this);
    this.totalPrice = ko.computed(function() {
      // Includes the eventdays
      return (this.totalTentsPrice() + this.totalEquipmentPrice()) * this.eventdays() + this.transportPrice();
    }, this);
    this.totalMinPersons = ko.computed(function() {
      return this._count_objs_attribute(this.selectedTents(), 'minPersons');
    }, this);
    this.totalMaxPersons = ko.computed(function() {
      return this._count_objs_attribute(this.selectedTents(), 'maxPersons');
    }, this);
    this.totalSquareMeters = ko.computed(function() {
      return this._count_objs_attribute(this.selectedTents(), 'squareMeter');
    }, this);
  }
  _count_objs_attribute(objs, attr) {
    let total = 0;
    objs.forEach(function(obj) { total += obj[attr]; });
    return total;
  }
};

kah.tentconfigurator.Wizard = class Wizard {
  constructor() {
    this.debug = false;
    if (typeof ajaxurl === 'undefined' ) {
      // wordpress sets the ajaxurl in the global scope.
      // If the ajaxurl is not initialized, we are not in a wordpress
      // environment.
      this.debug = true;
    }
    this.loadData();
    this.form = new kah.tentconfigurator.WizardForm();
    // 0 = Fillout, 1 = Success, 2 = Error, 3 = pending, 4 = FormValidation error
    this.mode = ko.observable(0);
    this.submitted = ko.observable(false);
    this.steps = ko.observableArray([
      new kah.tentconfigurator.WizardStepTents('Zelt', this.form),
      new kah.tentconfigurator.WizardStepEquipment('Zubehör', this.form),
      new kah.tentconfigurator.WizardStepEvent('Anlass', this.form),
      new kah.tentconfigurator.WizardStepContact('Kontakt', this.form)
    ]);
    this.addressNotFound = ko.observable(false);
    this.currentStep = ko.observable(this.steps()[0]);
    this.isLastStep = ko.computed(function() {
      return this.currentWizardStepId() >= this.steps().length - 1;
    }, this);
    this.showPrevButton = ko.computed(function() {
      return this.currentWizardStepId() > 0;
    }, this);
    this.nextButtonText = ko.computed(function() {
      if ( this.isLastStep() ) {
        return "Offerte anfordern";
      }
      return "Weiter";
    }, this);
    this.showNextButton = true;
    ko.computed(function() {
      for (let step of this.steps()) {
        step.active(false);
        if ( this.currentStep() == step ) {
          step.active(true);
        }
      }
    }, this);
    jQuery(document).on('focusout', 'input[required], select[required]', function(self) {
      return function() {
        self.validateField(this);
      };
    }(this));
    kah.tentconfigurator.googleAPILoaded.then(function(self) {
      return function() {
        self.initGoogleSearch();
      };
    }(this));
  }
  next() {
    if ( this.isLastStep() ) {
      this.send();
    } else {
      this.currentStep(this.steps()[this.currentWizardStepId() + 1]);
    }
  }
  previous() {
    this.currentStep(this.steps()[this.currentWizardStepId() -1]);
  }
  loadData() {
    let self = this;
    let request = this._make_request({action: 'kah_get_data'});
    request.then(
      function(response) {
        let tents = [];
        let equipment = [];
        if (self.debug) {
          tents = [
            new kah.tentconfigurator.Tent(1, 'Zelttitel 1', 0, 4, 20, 100, 150),
            new kah.tentconfigurator.Tent(2, 'Zelttitel 2', 30, 8, 20, 200, 300),
            new kah.tentconfigurator.Tent(3, 'Zelttitel 3', 50, 12, 20, 150, 300),
          ];
          equipment = [
            new kah.tentconfigurator.Equipment(1, 'Lampe', 5, 'kleine Lampe für das DJ Pult'),
            new kah.tentconfigurator.Equipment(2, 'Bodenplatte 30x30 cm', 0, 'Um den Boden zu schützen'),
            new kah.tentconfigurator.Equipment(3, 'Tisch - 3 Meter ', 9, "für 6 Personen"),
            new kah.tentconfigurator.Equipment(4, 'Tisch - 5 Meter ', 9, "für 9 Personen")
          ];
        } else {
          response.tents.forEach(function(tent) {
            tents.push(new kah.tentconfigurator.Tent(
              tent.id,
              tent.title,
              tent.price,
              tent.length,
              tent.width,
              tent.minPersons,
              tent.maxPersons));
          });
          response.equipment.forEach(function(equipmentItem) {
            equipment.push(new kah.tentconfigurator.Equipment(
              equipmentItem.id,
              equipmentItem.title,
              equipmentItem.price,
              equipmentItem.description));
          });
        }
        self.form.tents(tents);
        self.form.equipment(equipment);
      });
  }
  send() {
    jQuery(window).scrollTop(0);
    if ( !this.validateForm() ) {
      this.mode(4);
      return;
    }
    this.mode(3);
    let self = this;
    let data = {
      action: 'kah_send_offer',
      salutation: this.form.salutation(),
      firstName: this.form.firstName(),
      lastName: this.form.lastName(),
      company: this.form.company(),
      street: this.form.street(),
      streetnr: this.form.streetnr(),
      zip: this.form.zip(),
      location: this.form.location(),
      email: this.form.email(),
      phone: this.form.phone(),
      comment: this.form.comment(),
      acceptTerms: this.form.acceptTerms(),
      eventname: this.form.eventname(),
      eventdays: this.form.eventdays(),
      eventlocation: this.form.eventlocation(),
      eventlocationdistance: this.form.eventlocationdistance(),
      eventstartdate: this.form.eventstartdate(),
      transportPrice: this.form.transportPrice(),
      totalTentsPrice: this.form.totalTentsPrice(),
      totalEquipmentPrice: this.form.totalEquipmentPrice(),
      totalPrice: this.form.totalPrice(),
      totalMinPersons: this.form.totalMinPersons(),
      totalMaxPersons: this.form.totalMaxPersons(),
      totalSquareMeters: this.form.totalSquareMeters(),
      selectedTents: this.form.selectedTents(),
      selectedEquipment: this.form.selectedEquipment()
    };

    let request = this._make_request(data);
    request.then(
      function() { self.mode(1); self.submitted(true)},
      function() { self.mode(2); });
  }
  readablePrice(price) {
    return price + ' CHF';
  }
  readablePersons(minPersons, maxPersons) {
    return minPersons + ' - ' + maxPersons + ' Personen';
  }
  currentWizardStepId() {
    return this.steps().indexOf(this.currentStep());
  }
  _make_request(data) {
    let self = this;
    return new Promise(function(resolve, reject) {

      if ( self.debug ) {
        window.setTimeout(function() {
          resolve();
        }, 2000);
      } else {
        jQuery.post(ajaxurl, data)
          .done(function(response) { 
            if ( typeof(response.success) !== 'undefined' && !response.success ) {
              reject();
            } else {
              resolve(response);
            }
          })
          .fail(function(error) { reject(error); });
      }
    });
  }
  validateForm() {
    let isInvalidForm = false;
    jQuery('#wizard-form input[required], #wizard-form select[required]').each(function(self) {
      return function(counter, field) {
        if ( !self.validateField(field) ) { 
          if ( !isInvalidForm ) {
            self.currentStep(ko.dataFor(jQuery(field).closest('.wizardStepView')[0]));
            isInvalidForm = true;
          }
        }
      };
    }(this));
    return !isInvalidForm;  // Returns true if the form is valid
  }
  validateField(field) {
    let msgBox =jQuery('.formValidationError', jQuery(field).closest('td'))[0];
    if ( field.checkValidity() ) {
      jQuery(msgBox).hide();
      return true;
    } else {
      msgBox.innerHTML = field.validationMessage;
      jQuery(msgBox).show();
      return false;
    }
  }
  calculateDistance(place) {
    let self = this;
    if (typeof(place) === 'undefined') {
      self.addressNotFound(true);
      return;
    }

    let origin = 'Hauptstrasse 95, 1715 Alterswil, Schweiz';
    let destination = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
    let service = new google.maps.DistanceMatrixService();
    service.getDistanceMatrix({
      origins: [origin],
      destinations: [destination],
      travelMode: google.maps.TravelMode.DRIVING,},
      function(response, status) {
        if (status == google.maps.DistanceMatrixStatus.OK) {
          let distancematrix = response.rows[0].elements[0];
          if (distancematrix.status == google.maps.DistanceMatrixStatus.OK) {
            self.form.eventlocationdistance(distancematrix.distance.value);
            self.addressNotFound(false);
          }
        }
      }
    );

  }
  initGoogleSearch() {
    let input = document.getElementById('eventlocation');
    let searchBox = new google.maps.places.SearchBox(input);
    searchBox.addListener('places_changed', function(self) {
      return function() {
        self.calculateDistance(searchBox.getPlaces()[0]);
      };
    }(this));
    google.maps.event.addDomListener(input, 'keydown', function(e) { 
      if (e.keyCode == 13) { 
          e.preventDefault(); 
      }
    }); 
  };
};

kah.tentconfigurator.initGoogleSearch = function() {
  jQuery(window).trigger('kahGoogleAPILoaded');
};

jQuery(window).on('load', function() {
  if (jQuery('#wizard-form').length > 0) {
    ko.applyBindings(new kah.tentconfigurator.Wizard(), document.getElementById('wizard-form'));  
  }
});
