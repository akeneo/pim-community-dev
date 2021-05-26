import {getAppcuesAgent} from './appcues-agent';
import {PimOnBoarding} from 'pimui/js/onboarding';

const FeatureFlags = require('pim/feature-flags');
const UserContext = require('pim/user-context');
const Mediator = require('oro/mediator');

const AppcuesOnBoarding: PimOnBoarding = {
  registerUser: () => {
    getAppcuesAgent().then(appcues => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.identify(UserContext.get('meta').id, {
        username: UserContext.get('username'),
        email: UserContext.get('email'),
        language: UserContext.get('uiLocale'),
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
      AppcuesOnBoarding.page();
    });
    AppcuesOnBoarding.registerUser();
  },
};

export = AppcuesOnBoarding;
