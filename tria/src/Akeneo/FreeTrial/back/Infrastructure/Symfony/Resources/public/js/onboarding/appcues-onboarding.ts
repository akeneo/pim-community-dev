import {getAppcuesAgent} from './appcues-agent';
import {PimOnboarding} from './pim-onboarding';
import {Category} from '@akeneo-pim-community/shared';

const _ = require('underscore');
const FeatureFlags = require('pim/feature-flags');
const UserContext = require('pim/user-context');
const Mediator = require('oro/mediator');
const Router = require('pim/router');

interface EventOptions {
  code?: string;
  codes?: [];
  name?: string;
  attribute?: string;
  gridName?: string;
  identifier?: string;
  value?: string;
  column?: string;
  localeCode?: string;
  context?: string;
  count?: number;
  entityHint?: string;
  model?: {
    attributes?: {
      identifier?: string;
      code?: string;
      label?: string;
    };
  };
  attributes?: {
    identifier?: string;
    code?: string;
    label?: string;
  };
  url?: string;
  type?: string;
  inputName?: string;
  actions?: [Action];
  values?: object;
}

interface Action {
  attribute_code?: string;
  channel_code?: string;
  is_required?: boolean;
}

interface Event {
  name?: string;
  checklistName?: string;
  flowName?: string;
  flowId?: string;
}

const FLOW_GUIDED_TOUR_ID = 'd413bbd2-02cf-4664-bcd2-1e799624f639';
const CATEGORY_PAIN_MANAGEMENT_CODE = '008_1_1';

const AppcuesOnboarding: PimOnboarding = {
  track: (event: string, eventOptions?: EventOptions) => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      switch (event) {
        case 'navigation:entry:clicked':
          if (eventOptions && eventOptions.code) {
            appcues.track('Navigation entry "' + eventOptions.code + '" clicked');
          }
          break;
        case 'grid:item:selected':
          if (eventOptions) {
            if (eventOptions.name === 'product-grid' && eventOptions.entityHint === 'product') {
              if (
                eventOptions.model &&
                eventOptions.model.attributes &&
                eventOptions.model.attributes.identifier === 'PLGCHAELK001'
              ) {
                appcues.track('Product "Elka Peacock Armchair" selected');
              }

              if (
                eventOptions.model &&
                eventOptions.model.attributes &&
                eventOptions.model.attributes.identifier === 'BFGoodrich - Advantage T/A Sport'
              ) {
                appcues.track('Product model "BFGoodrich - Advantage T/A Sport" selected');
              }

              appcues.track('Product selected');
            }

            if (eventOptions.name === 'export-profile-grid' && eventOptions.entityHint === 'export profile') {
              if (
                eventOptions.model &&
                eventOptions.model.attributes &&
                eventOptions.model.attributes.code === 'printers_amazon'
              ) {
                appcues.track('Export profile "Printers for Amazon (weekly)" selected');
              }

              appcues.track('Export profile selected');
            }

            if (eventOptions.name === 'family-grid' && eventOptions.entityHint === 'family') {
              if (
                eventOptions.model &&
                eventOptions.model.attributes &&
                eventOptions.model.attributes.label === 'Tires'
              ) {
                appcues.track('Family "Tires" selected');
              }

              appcues.track('Family selected');
            }
          }
          break;
        case 'grid:item:number-selected':
          if (eventOptions && eventOptions.inputName === 'family-grid') {
            if (eventOptions.count && eventOptions.count === 3) {
              appcues.track('3 families selected in the grid');
            }
          }
          break;
        case 'grid:item:all-selected':
          if (eventOptions && eventOptions.inputName === 'product-grid') {
            appcues.track('All products are selected');
          }
          break;
        case 'product-grid:view:selected':
          if (eventOptions && eventOptions.name) {
            appcues.track('View "' + eventOptions.name + '" selected');
          }

          appcues.track('View selected');
          break;
        case 'product-grid:view:saved':
          if (eventOptions && eventOptions.name) {
            appcues.track('View "' + eventOptions.name + '" saved');
          }

          appcues.track('View saved');
          break;
        case 'product-grid:column:selected':
          if (
            eventOptions &&
            eventOptions.gridName === 'product-grid' &&
            eventOptions.column &&
            eventOptions.column.includes('designer')
          ) {
            appcues.track('Column "Designer" added in the product grid');
          }

          appcues.track('Column added in the product grid');
          break;
        case 'product-grid:attribute-group:selected':
          if (eventOptions && eventOptions.code === 'contentcopy') {
            appcues.track('Attribute group "Content / Copy" selected');
          }

          if (eventOptions && eventOptions.code === 'specifications') {
            appcues.track('Attribute group "Specifications / Product Team" selected');
          }

          appcues.track('Attribute group selected in the product grid');
          break;
        case 'product:form:compare-clicked':
          appcues.track('Compare button clicked');
          break;
        case 'product:form:locale-switched':
          if (eventOptions && eventOptions.context === 'base_product' && eventOptions.localeCode) {
            appcues.track('Product\'s locale switched to "' + eventOptions.localeCode + '"');
          }

          if (eventOptions && eventOptions.context === 'copy_product' && eventOptions.localeCode) {
            appcues.track('Compare\'s locale switched to "' + eventOptions.localeCode + '"');
          }
          break;
        case 'product-grid:completeness:opened':
          if (eventOptions && eventOptions.name === 'PLGCHAELK001') {
            appcues.track('Completeness badge for product "Elka Peacock Armchair" opened');
          }

          appcues.track('Completeness badge opened in product edit form');
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
        case 'product-model:form:variant-selected':
          appcues.track('Variant selected from product model');
          break;
        case 'grid:mass-edit:clicked':
          if (eventOptions && eventOptions.name === 'product-edit') {
            appcues.track('Button "Bulk actions" in product grid clicked');
          }

          if (eventOptions && eventOptions.name === 'family-edit') {
            appcues.track('Button "Bulk actions" in family grid clicked');
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
        case 'grid:mass-edit:item-chosen':
          if (eventOptions && eventOptions.code === 'add_attribute_value') {
            appcues.track('Bulk action "Add attribute values" selected');
          }

          if (eventOptions && eventOptions.code === 'set_requirements') {
            appcues.track('Bulk action "Set attributes requirements" selected');
          }
          break;
        case 'grid:mass-edit:attributes-added':
          if (eventOptions && eventOptions.codes) {
            const codes = eventOptions.codes.join(',');

            if (codes.includes('certifications')) {
              appcues.track('Attribute "Certifications" added in a bulk action');
            }

            if (codes.includes('food_standard')) {
              appcues.track('Attribute "Industry Standards" added in a bulk action');
            }
          }

          appcues.track('Attribute added in a bulk action');
          break;
        case 'family-grid:mass-edit:attributes-added':
          if (eventOptions && eventOptions.codes) {
            const codes = eventOptions.codes.join(',');

            if (codes.includes('photo_printing')) {
              appcues.track('Attribute "Photo printing" added in a bulk action');
            }
          }
          break;
        case 'product-grid:mass-edit:attributes-added':
          if (eventOptions && eventOptions.values) {
            _.each(eventOptions.values, function (attributeArray: any, code: string) {
              const data = attributeArray['0']['data'];
              const attributeOptions = data.join(',');

              if (code === 'certifications' && attributeOptions.includes('vegan')) {
                appcues.track('Option "Vegan" added from the attribute "Certifications" in a bulk action');
              }

              if (code === 'food_standard' && attributeOptions.includes('red_tractor')) {
                appcues.track('Option "Red Tractor" added from the attribute "Industry Standards" in a bulk action');
              }
            });
          }
          break;
        case 'grid:mass-edit:requirements-checked':
          if (eventOptions && eventOptions.actions) {
            _.each(eventOptions.actions, function (action: Action) {
              if (action.channel_code === 'marketplaces' && action.is_required) {
                appcues.track('The information is required for Marketplaces channel');
              }
            });
          }
          break;
        case 'settings:attributes:clicked':
          appcues.track('Settings: "Attributes" clicked');
          break;
        case 'settings:families:clicked':
          appcues.track('Settings: "Families" clicked');
          break;
        case 'attribute:create:type-selected':
          if (eventOptions && eventOptions.type) {
            appcues.track('Attribute of type "' + eventOptions.type + '" created');
          }
          break;
        case 'common:form:value-changed':
          if (eventOptions && eventOptions.code && eventOptions.code.includes('pim-attribute') && eventOptions.name) {
            appcues.track('On attribute form, the value of field "' + eventOptions.name + '" changed');
          }
          break;
        case 'translation:form:value-changed':
          if (
            eventOptions &&
            eventOptions.code &&
            eventOptions.code.includes('pim-attribute') &&
            eventOptions.localeCode
          ) {
            appcues.track('On attribute form, the translation label of "' + eventOptions.localeCode + '" changed');
          }
          break;
        case 'common:form:saved':
          if (eventOptions && eventOptions.code && eventOptions.code.includes('pim-attribute-create')) {
            appcues.track('Create attribute form saved');
          }
          break;
        case 'family:variant:attribute-set':
          if (eventOptions && eventOptions.codes) {
            const codes = eventOptions.codes.join(',');

            if (codes.includes('meta_title')) {
              appcues.track('Attribute "Meta title" added as family variant');
            }
          }
          break;
        case 'family:variant:attribute-remove':
          if (eventOptions && eventOptions.codes) {
            const codes = eventOptions.codes.join(',');

            if (codes.includes('winter_designed_tire')) {
              appcues.track('Attribute "Winter Designed Tire" added as family variant');
            }
          }
          break;
        case 'family:variant:saved':
          appcues.track('Family variant saved');
          break;
        case 'family:edit:variant-selected':
          if (eventOptions && eventOptions.code === 'pim-family-edit-form-variant') {
            appcues.track('Tab "Variants" selected in family edit form');
          }
          break;
        case 'form:edit:opened':
          if (eventOptions && eventOptions.code === 'pim-job-instance-xlsx-product-export-edit') {
            if (
              eventOptions.model &&
              eventOptions.model.attributes &&
              eventOptions.model.attributes.code === 'printers_amazon'
            ) {
              appcues.track('Edit export profile "Printers for Amazon (weekly)"');
            }

            appcues.track('Edit export profile');
          }
          break;
        case 'export-profile:product:content-tab-opened':
          if (eventOptions && eventOptions.code === 'pim-job-instance-xlsx-product-export-edit-content') {
            appcues.track('Content tab opened on edit export profile product');
          }
          break;
        case 'export-profile:product:attribute-added':
          if (eventOptions && eventOptions.column && eventOptions.column.includes('automatic_two_sided_printing')) {
            appcues.track('Attribute "Automatic Two-Sided Printing" added in the content of the export profile');
          }
          break;
        case 'export-profile:product:attribute-applied':
          appcues.track('Selection of attributes in the content of the export profile applied');
          break;
        case 'job-instance:form-edit:saved':
          if (eventOptions && eventOptions.code === 'printers_amazon') {
            appcues.track('Edit export profile "Printers for Amazon (weekly)" saved');
          }
          break;
        case 'job-instance:export:launched':
          if (eventOptions && eventOptions.url && eventOptions.url.includes('printers_amazon')) {
            appcues.track('Export profile "Printers for Amazon (weekly)" launched');
          }
          break;
        default:
          appcues.track(event);
      }
    });
  },
  init: async () => {
    const appcues = await getAppcuesAgent();
    if (appcues === null) {
      return;
    }

    Mediator.on('route_complete', async () => {
      appcues.page();
    });

    appcues.identify(UserContext.get('username'), {
      email: UserContext.get('email'),
      first_name: UserContext.get('first_name'),
      last_name: UserContext.get('last_name'),
    });

    appcues.loadLaunchpad('#appcues-launchpad-btn', {
      position: 'left',
      header: "<p style='font-size: 18px;'>Tutorials</p>",
      icon: '/bundles/akeneofreetrial/icons/LaunchpadIcon.svg',
    });

    appcues.on('checklist_completed', (event: Event) => {
      AppcuesOnboarding.track(event.name + ': ' + event.checklistName);
    });

    appcues.on('checklist_dismissed', (event: Event) => {
      AppcuesOnboarding.track(event.name + ': ' + event.checklistName);
    });

    appcues.on('flow_started', async (event: Event) => {
      if (event.flowId === FLOW_GUIDED_TOUR_ID) {
        // Set the "Pain Management" category id in session in order to deploy it when the user has launched the flow "FT - Guided Tour"
        const categoryRoute = Router.generate('pim_enrich_category_rest_get', {
          identifier: CATEGORY_PAIN_MANAGEMENT_CODE,
        });
        const categoryResponse = await fetch(categoryRoute);
        const categoryPainManagement: Category = await categoryResponse.json();

        sessionStorage.setItem(
          'lastSelectedCategory',
          JSON.stringify({treeId: '1', categoryId: categoryPainManagement.id})
        );
      }
    });
  },
};

export = AppcuesOnboarding;
