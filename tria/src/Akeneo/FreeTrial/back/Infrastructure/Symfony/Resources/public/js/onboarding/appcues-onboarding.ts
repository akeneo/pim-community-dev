import {getAppcuesAgent} from './appcues-agent';
import {PimOnboarding} from './pim-onboarding';

const FeatureFlags = require('pim/feature-flags');
const UserContext = require('pim/user-context');
const Mediator = require('oro/mediator');

interface EventOptions {
  name?: string,
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
        case 'product-grid:view:selected':
          if (eventOptions && eventOptions.name === 'Furniture - To enrich') {
            appcues.track('View "Furniture - To enrich" selected');
          }
          break;
        case 'product-grid:column:selected':
          if (eventOptions && eventOptions.name && eventOptions.name.includes('designer')) {
            appcues.track('Column "Designer" added in the product grid');
          }
          break;
        case 'product-grid:product:selected':
          if (eventOptions && eventOptions.name === 'PLGCHAELK001') {
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
