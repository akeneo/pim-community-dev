import {getAppcuesAgent} from './appcues-agent';
import {PimOnBoarding} from 'pimui/js/onboarding';

const FeatureFlags = require('pim/feature-flags');
const UserContext = require('pim/user-context');

const AppcuesOnBoarding: PimOnBoarding = {
  registerUser: () => {
    getAppcuesAgent().then((appcues) => {
      if (!FeatureFlags.isEnabled('free_trial') || appcues === null) {
        return;
      }

      appcues.identify(UserContext.get('meta').id, {
        username: UserContext.get('username'),
        first_name: UserContext.get('first_name'),
        email: UserContext.get('email'),
        language: UserContext.get('uiLocale'),
      });
    });
  },
  page: () => {},
  track: () => {},
};

export = AppcuesOnBoarding;
