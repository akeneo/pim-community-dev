const PimApp = require('pimui/js/pim-app');
const PimOnboarding = require('pim/free-trial/onboarding');
const PimMessaging = require('pim/free-trial/messaging');
const PimGoogleAnalytics = require('pim/free-trial/google-analytics');
const Heap = require('pim/free-trial/heap');
const FeatureFlags = require('pim/feature-flags');

class FreeTrialPimApp extends PimApp {
  public configure() {
    if (FeatureFlags.isEnabled('free_trial')) {
      return super.configure().then(() => {
        PimOnboarding.init();
        PimMessaging.init();
        PimGoogleAnalytics.init();
        Heap.init();
      });
    }

    return super.configure();
  }
}

export = FreeTrialPimApp;
