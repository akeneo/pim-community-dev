import {getAppcuesAgent} from './appcues-agent';
import {PimOnboarding} from 'pimui/js/onboarding';

const FeatureFlags = require('pim/feature-flags');
const UserContext = require('pim/user-context');
const Mediator = require('oro/mediator');

const AppcuesOnboarding: PimOnboarding = {
  registerUser: () => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.identify(UserContext.get('username'), {
        id: UserContext.get('meta').id,
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
  track: () => {},
  init: () => {
    Mediator.on('route_complete', async () => {
      AppcuesOnboarding.page();
    });
    AppcuesOnboarding.registerUser();
  },
};

export = AppcuesOnboarding;
