const PimApp = require('pimui/js/pim-app');
const PimOnboarding = require('pim/free-trial/onboarding');
const PimMessaging = require('pim/free-trial/messaging');
const PimGoogleAnalytics = require('pim/free-trial/google-analytics');
const Heap = require('pim/free-trial/heap');

class FreeTrialPimApp extends PimApp {
  public configure() {
    return super.configure().then(() => {
      PimOnboarding.init();
      PimMessaging.init();
      PimGoogleAnalytics.init();
      Heap.init();
    });
  }
}

export = FreeTrialPimApp;
