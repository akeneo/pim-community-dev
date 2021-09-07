import {getAppcuesAgent} from './appcues-agent';
import {PimOnboarding} from './pim-onboarding';

const FeatureFlags = require('pim/feature-flags');
const UserContext = require('pim/user-context');
const Mediator = require('oro/mediator');

interface EventOptions {
  name?: string,
  attribute?: string,
  gridName?: string,
  identifier?: string,
  value?: string,
  column?: string,
}

const AppcuesOnboarding: PimOnboarding = {
  registerUser: () => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.identify(UserContext.get('username'));
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
          break;
        case 'product-grid:view:selected':
          if (eventOptions && eventOptions.name === 'Furniture - To enrich') {
            appcues.track('View "Furniture - To enrich" selected');
          }

          if (eventOptions && eventOptions.name === 'Food - To enrich') {
            appcues.track('View "Food - To enrich" selected');
          }
          break;
        case 'product-grid:column:selected':
          if (eventOptions && eventOptions.gridName === 'product-grid' && eventOptions.column && eventOptions.column.includes('designer')) {
            appcues.track('Column "Designer" added in the product grid');
          }
          break;
        case 'product-grid:product:selected':
          if (eventOptions && eventOptions.identifier === 'PLGCHAELK001') {
            appcues.track('Product "Elka Peacock Armchair" selected');
          }
          break;
        case 'product-grid:completeness:opened':
          if (eventOptions && eventOptions.name === 'PLGCHAELK001') {
            appcues.track('Completeness badge for product "Elka Peacock Armchair" opened');
          }
          break;
        case 'product-grid:attribute-group:selected':
          if (eventOptions && eventOptions.name === 'contentcopy') {
            appcues.track('Attribute group "Content / Copy" selected');
          }
          break;
        case 'product:attribute-value:updated':
          if (eventOptions && eventOptions.attribute === 'designer' && eventOptions.value === 'studio_plumen') {
            appcues.track('Attribute "Designer" filled with "Studio Plumen"');
          }
          break;
        case 'product:form:saved':
          if (eventOptions && eventOptions.name === 'PLGCHAELK001') {
            appcues.track('Product "Elka Peacock Armchair" saved');
          }
          break;
        case 'product-grid:product:all-selected':
          appcues.track('All products are selected');
          break;
        case 'grid:mass-edit:clicked':
          if (eventOptions && eventOptions.name === 'product-edit') {
            appcues.track('Button "Bulk actions" in product grid clicked');
          }
          break;
        case 'grid:mass-edit:item-chosen':
          if (eventOptions && eventOptions.name === 'add_attribute_value') {
            appcues.track('Bulk action "Add attribute values" selected');
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
        header: "<p style='font-size: 18px;'>Checklists</p>",
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
