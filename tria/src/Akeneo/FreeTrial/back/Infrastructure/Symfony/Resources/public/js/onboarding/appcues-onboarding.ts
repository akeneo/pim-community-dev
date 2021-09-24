import {getAppcuesAgent} from './appcues-agent';
import {PimOnboarding} from './pim-onboarding';

const FeatureFlags = require('pim/feature-flags');
const UserContext = require('pim/user-context');
const Mediator = require('oro/mediator');

interface EventOptions {
  code?: string,
  name?: string,
  attribute?: string,
  gridName?: string,
  identifier?: string,
  value?: string,
  column?: string,
  localeCode?: string,
  context?: string,
  count?: number,
  checked?: boolean,
  entityHint?: string,
  attributes?: {
    identifier?: string,
    code?: string,
    label?: string,
  },
  url?: string,
}

const AppcuesOnboarding: PimOnboarding = {
  registerUser: () => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.identify(UserContext.get('username'), {
        'email': UserContext.get('email'),
        'first_name': UserContext.get('first_name'),
        'last_name': UserContext.get('last_name'),
      });
    });
  },
  page: () => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.page();
    });
  },
  track: (event: string, eventOptions?: EventOptions) => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      switch (event) {
        case 'product-grid:view:saved':
          if (eventOptions && eventOptions.name === 'Furniture - To enrich') {
            appcues.track('View "Furniture - To enrich" saved');
          }

          appcues.track('View saved');
          break;
        case 'product-grid:view:selected':
          if (eventOptions && eventOptions.name === 'Furniture - To enrich') {
            appcues.track('View "Furniture - To enrich" selected');
          }

          if (eventOptions && eventOptions.name === 'Food - To enrich') {
            appcues.track('View "Food - To enrich" selected');
          }

          appcues.track('View selected');
          break;
        case 'product-grid:column:selected':
          if (eventOptions && eventOptions.gridName === 'product-grid' && eventOptions.column && eventOptions.column.includes('designer')) {
            appcues.track('Column "Designer" added in the product grid');
          }

          appcues.track('Column added in the product grid');
          break;
        case 'grid:item:selected':
          if (eventOptions && eventOptions.name === 'product-grid' && eventOptions.entityHint && eventOptions.entityHint === 'product') {
            if (eventOptions.attributes && eventOptions.attributes.identifier === 'PLGCHAELK001') {
              appcues.track('Product "Elka Peacock Armchair" selected');
            }

            if (eventOptions.attributes && eventOptions.attributes.identifier === 'BFGoodrich - Advantage T/A Sport') {
              appcues.track('Product model "BFGoodrich - Advantage T/A Sport" selected');
            }

            appcues.track('Product selected');
          }

          if (eventOptions && eventOptions.name === 'export-profile-grid' && eventOptions.entityHint && eventOptions.entityHint === 'export profile') {
            if (eventOptions.attributes && eventOptions.attributes.code === 'printers_amazon') {
              appcues.track('Export profile "Printers for Amazon (weekly)" selected');
            }

            appcues.track('Export profile selected');
          }

          if (eventOptions && eventOptions.name === 'family-grid' && eventOptions.entityHint && eventOptions.entityHint === 'family') {
            if (eventOptions.attributes && eventOptions.attributes.label === 'Tires') {
              appcues.track('Family "Tires" selected');
            }

            appcues.track('Family selected');
          }
          break;
        case 'product-grid:completeness:opened':
          if (eventOptions && eventOptions.name === 'PLGCHAELK001') {
            appcues.track('Completeness badge for product "Elka Peacock Armchair" opened');
          }

          appcues.track('Completeness badge opened in product edit form');
          break;
        case 'product-grid:attribute-group:selected':
          if (eventOptions && eventOptions.name === 'contentcopy') {
            appcues.track('Attribute group "Content / Copy" selected');
          }

          if (eventOptions && eventOptions.name === 'specifications') {
            appcues.track('Attribute group "Specifications / Product Team" selected');
          }

          appcues.track('Attribute group selected in the product grid');
          break;
        case 'product:attribute-value:updated':
          if (eventOptions && eventOptions.attribute === 'winter_designed_tire' && eventOptions.value) {
            appcues.track('Attribute "Winter designed Tire" changed to Yes value');
          }

          if (eventOptions && eventOptions.attribute) {
            appcues.track('Attribute "' + eventOptions.attribute + '" filled in product edit form');
          }
          break;
        case 'product:form:saved':
          if (eventOptions && eventOptions.name === 'PLGCHAELK001') {
            appcues.track('Product "Elka Peacock Armchair" saved');
          }

          if (eventOptions && eventOptions.name === 'PLG513725') {
            appcues.track('Product "Faux leather tote" saved');
          }

          appcues.track('Product saved');
          break;
        case 'product-model:form:saved':
          if (eventOptions && eventOptions.code === 'BFGoodrich - Advantage T/A Sport') {
            appcues.track('Product model "BFGoodrich - Advantage T/A Sport" saved');
          }

          appcues.track('Product model saved');
          break;
        case 'product-grid:product:all-selected':
          appcues.track('All products are selected');
          break;
        case 'grid:mass-edit:clicked':
          if (eventOptions && eventOptions.name === 'product-edit') {
            appcues.track('Button "Bulk actions" in product grid clicked');
          }

          if (eventOptions && eventOptions.name === 'family-edit') {
            appcues.track('Button "Bulk actions" in family grid clicked');
          }
          break;
        case 'grid:mass-edit:item-chosen':
          if (eventOptions && eventOptions.name === 'add_attribute_value') {
            appcues.track('Bulk action "Add attribute values" selected');
          }

          if (eventOptions && eventOptions.name === 'set_requirements') {
            appcues.track('Bulk action "Set attributes requirements" selected');
          }
          break;
        case 'grid:mass-edit:action-step':
          if (eventOptions && eventOptions.name === 'configure') {
            appcues.track('Clicked on "Next" after choosing a bulk action');
          }

          if (eventOptions && eventOptions.name === 'validate') {
            appcues.track('Clicked on "Confirm" after configuring a bulk action');
          }
          break;
        case 'grid:mass-edit:attributes-added':
          if (eventOptions && eventOptions.name && eventOptions.name.includes('certifications')) {
            appcues.track('Attribute "Certifications" added in a bulk action');
          }

          if (eventOptions && eventOptions.name && eventOptions.name.includes('food_standard')) {
            appcues.track('Attribute "Industry Standards" added in a bulk action');
          }

          appcues.track('Attribute added in a bulk action');
          break;
        case 'product:form:compare-clicked':
          appcues.track('Compare button clicked');
          break;
        case 'product:form:locale-switched':
          if (eventOptions && eventOptions.context && eventOptions.context === 'base_product' && eventOptions.localeCode) {
            appcues.track('Product\'s locale switched to "' + eventOptions.localeCode + '"');
          }

          if (eventOptions && eventOptions.context && eventOptions.context === 'copy_product' && eventOptions.localeCode) {
            appcues.track('Compare\'s locale switched to "' + eventOptions.localeCode + '"');
          }
          break;
        case 'settings:attributes:clicked':
          appcues.track('Settings: "Attributes" clicked');
          break;
        case 'settings:families:clicked':
          appcues.track('Settings: "Families" clicked');
          break;
        case 'attribute:create:type-selected':
          if (eventOptions && eventOptions.name) {
            appcues.track('Attribute of type "' + eventOptions.name +'" created');
          }
          break;
        case 'common:form:value-changed':
          if (eventOptions && eventOptions.code && eventOptions.code.includes('pim-attribute') && eventOptions.name) {
            appcues.track('On attribute form, the value of field "' + eventOptions.name +'" changed');
          }
          break;
        case 'translation:form:value-changed':
          if (eventOptions && eventOptions.code && eventOptions.code.includes('pim-attribute') && eventOptions.localeCode) {
            appcues.track('On attribute form, the translation label of "' + eventOptions.localeCode +'" changed');
          }
          break;
        case 'common:form:saved':
          if (eventOptions && eventOptions.code && eventOptions.code.includes('pim-attribute-create')) {
            appcues.track('Create attribute form saved');
          }
          break;
        case 'family-grid:product:item-selected':
          if (eventOptions && eventOptions.count && eventOptions.count === 3) {
            appcues.track('3 families selected in the grid');
          }
          break;
        case 'grid:mass-edit:requirements-checked':
          if (eventOptions && eventOptions.code && eventOptions.code === 'marketplaces' && eventOptions.checked) {
            appcues.track('The information is required for Marketplaces channel');
          }
          break;
        case 'form:edit:opened':
          if (eventOptions && eventOptions.code && eventOptions.code === 'pim-job-instance-xlsx-product-export-edit') {
            if (eventOptions.attributes && eventOptions.attributes.code === 'printers_amazon') {
              appcues.track('Edit export profile "Printers for Amazon (weekly)"');
            }

            appcues.track('Edit export profile');
          }
          break;
        case 'export-profile:product:content-tab-opened':
          if (eventOptions && eventOptions.code && eventOptions.code === 'pim-job-instance-xlsx-product-export-edit-content') {
            appcues.track('Content tab opened on edit export profile product');
          }
          break;
        case 'export-profile:product:attribute-added':
          if (eventOptions && eventOptions.column && eventOptions.column.includes('automatic_two_sided_printing')) {
            appcues.track('Attribute "Automatic Two-Sided Printing" added in the content of the export profile');
          }
          break;
        case 'job-instance:form-edit:saved':
          if (eventOptions && eventOptions.code && eventOptions.code === 'printers_amazon') {
            appcues.track('Edit export profile "Printers for Amazon (weekly)" saved');
          }
          break;
        case 'job-instance:export:launched':
          if (eventOptions && eventOptions.url && eventOptions.url.includes('printers_amazon')) {
            appcues.track('Export profile "Printers for Amazon (weekly)" launched');
          }
          break;
        case 'product-model:form:variant-selected':
          appcues.track('Variant selected from product model');
          break;
        case 'family:edit:variant-selected':
          if (eventOptions && eventOptions.code && eventOptions.code === 'pim-family-edit-form-variant') {
            appcues.track('Tab "Variants" selected in family edit form');
          }
          break;
        case 'family:variant:attribute-set':
          if (eventOptions && eventOptions.name && eventOptions.name.includes('meta_title')) {
            appcues.track('Attribute "Meta title" added as family variant');
          }
          break;
        case 'navigation:entry:clicked':
          if (eventOptions && eventOptions.code) {
            appcues.track('Navigation entry "' + eventOptions.code +'" clicked');
          }
          break;
      }
    });
  },
  loadLaunchpad: (element: string) => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.loadLaunchpad(element, {
        position: "left",
        header: "<p style='font-size: 18px;'>Tutorials</p>",
        icon: '/bundles/akeneofreetrial/icons/LaunchpadIcon.svg'
      });
    });
  },
  init: () => {
    Mediator.on('route_complete', async () => {
      AppcuesOnboarding.page();
    });
    AppcuesOnboarding.registerUser();
    AppcuesOnboarding.loadLaunchpad('#appcues-launchpad-btn');
  },
};

export = AppcuesOnboarding;
