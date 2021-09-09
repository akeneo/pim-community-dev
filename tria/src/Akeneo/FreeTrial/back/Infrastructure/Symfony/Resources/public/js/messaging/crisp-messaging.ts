import {PimMessaging} from './pim-messaging';
import {getCrispAgent} from "./crisp-agent";

const FeatureFlags = require('pim/feature-flags');

const CrispMessaging: PimMessaging = {
  is: (action: string) => {
    getCrispAgent().then(crisp => {
      if (!FeatureFlags.isEnabled('free_trial') || crisp === null) {
        return;
      }

      return crisp.is(action);
    });
  },
  push: (elements: []) => {
    getCrispAgent().then(crisp => {
      if (!FeatureFlags.isEnabled('free_trial') || crisp === null) {
        return;
      }

      crisp.push(elements);
    });
  },
  init: () => {
    if (typeof window.$crisp === 'undefined' || typeof window.CRISP_WEBSITE_ID === 'undefined') {
      throw new Error('Crisp library is not installed');
    }

    window.CRISP_READY_TRIGGER = function() {
      if ($crisp.is("chat:opened") === true) {
        // Do something.
      }
    };
  },
};

export = CrispMessaging;
