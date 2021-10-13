import {Analytics} from '@akeneo-pim-community/shared';
import AppcuesOnboarding = require("./onboarding/appcues-onboarding");

const PimAnalytics: Analytics = {
  track(event: string, properties?: object) {
    AppcuesOnboarding.track(event, properties);
  },
};

export = PimAnalytics;
