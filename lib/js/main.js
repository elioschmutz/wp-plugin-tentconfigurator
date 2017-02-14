class Tent {
  constructor(name, price, length, width, minPersons, maxPersons) {
    this.name = name;
    this.price = price;
    this.length = length;
    this.width = width;
    this.minPersons = minPersons;
    this.maxPersons = maxPersons;
    this.squareMeter = this.length * this.width;
  }
}

class Equipment {
  constructor(title, price, description) {
    this.title = title;
    this.price = price; 
    this.description = description;
  }
}

var tents = [
  new Tent('Ein riesen grosses Zelt für alle', 15, 4, 20, 100, 150),
  new Tent('Grosses Zelt', 30, 8, 20, 200, 300),
  new Tent('Grosses Zelt', 50, 12, 20, 150, 300),
];

var equipments = [
  new Equipment('Lampe', 5, 'kleine Lampe für das DJ Pult'),
  new Equipment('Bodenplatte 30x30 cm', 2, 'Um den Boden zu schützen'),
  new Equipment('Tisch - 3 Meter ', 9, "für 6 Personen"),
  new Equipment('Tisch - 5 Meter ', 9, "für 9 Personen")
];

class WizardStep {
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
}

class WizardSubStep extends WizardStep {
  constructor(parent, ...args) {
    super(...args);
    this.parent = parent;
    this.isVisible = ko.computed(function() {
      return this.active() && this.parent.active();
    }, this);
  }
}
class WizardStepTents extends WizardStep {
  constructor(...args) {
    super(...args);
    this.selectTentsSubStep = new WizardStepSelectTents(this);
    this.openSubStep(this.selectTentsSubStep);
  }
  openSelectTent() {
    this.openSubStep(this.selectTentsSubStep);
  }
  removeDataRow(tent) {
    this.wizardForm.selectedTents.remove(tent);
  }
}

class WizardStepEquipments extends WizardStep {
  constructor(...args) {
    super(...args);
    this.selectEquipmentSubStep = new WizardStepSelectEquipment(this);
    this.openSubStep(this.selectEquipmentSubStep);
  }
  openSelectEquipment() {
    this.openSubStep(this.selectEquipmentSubStep);
  }
  removeDataRow(equipment) {
    this.wizardForm.selectedEquipments.remove(equipment);
  } 
}

class WizardStepContact extends WizardStep {
  constructor(...args) {
    super(...args);
    this.availableGenders = ko.observableArray(['Bitte auswählen...', 'Herr', 'Frau']);
    this.validLastName = ko.observable(true);
  }
  validateLastName() {
    this.validLastName(false);
  }
}

class WizardStepSelectTents extends WizardSubStep {
  constructor(parent, ...args) {
    super(parent, ...args);
  }
  selectTent(tent) {
    this.parent.wizardForm.selectedTents.push(Object.assign(new Tent(), tent));
    this.parent.closeSubStep();
  }
}

class WizardStepSelectEquipment extends WizardSubStep {
  selectEquipment(equipment) {
    this.parent.wizardForm.selectedEquipments.push(Object.assign(new Equipment(), equipment));
    this.parent.closeSubStep();
  }
}
class WizardForm {
  constructor() {
    this.firstName = ko.observable();
    this.lastName = ko.observable();
    this.tents = ko.observableArray(tents.sort((a, b) => a.minPersons - b.minPersons));
    this.equipments = ko.observableArray(equipments);
    this.selectedTents = ko.observableArray();
    this.selectedEquipments = ko.observableArray();
    this.totalTentsPrice = ko.computed(function() {
      return this._count_objs_attribute(this.selectedTents(), 'price');
    }, this);
    this.totalEquipmentsPrice = ko.computed(function() {
      return this._count_objs_attribute(this.selectedEquipments(), 'price');
    }, this);
    this.totalPrice = ko.computed(function() {
      return this.totalTentsPrice() + this.totalEquipmentsPrice();
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
}

class Wizard {
  constructor() {
    this.form = new WizardForm();
    this.steps = ko.observableArray([
      new WizardStepTents('Zelt', this.form),
      new WizardStepEquipments('Zubehör', this.form),
      new WizardStepContact('Kontakt', this.form)
    ]);
    this.currentStep = ko.observable(this.steps()[0]);
    this.showPrevButton = ko.computed(function() {
      return this.currentWizardStepId() > 0;
    }, this);
    this.showNextButton = ko.computed(function() {
      return this.currentWizardStepId() < (this.steps().length -1);
    }, this);
    this.showSendButton = ko.computed(function() {
      return this.currentWizardStepId() == this.steps().length -1;
    }, this);
    
    ko.computed(function() {
      for (let step of this.steps()) {
        step.active(false);
        if ( this.currentStep() == step ) {
          step.active(true);
        }
      }
    }, this);
  }
  next() {
    this.currentStep(this.steps()[this.currentWizardStepId() + 1]);
  }
  previous() {
    this.currentStep(this.steps()[this.currentWizardStepId() -1]);
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
}

jQuery(document).ready ( function(){
  ko.applyBindings(new Wizard(), document.getElementById('wizard-form'));
});
