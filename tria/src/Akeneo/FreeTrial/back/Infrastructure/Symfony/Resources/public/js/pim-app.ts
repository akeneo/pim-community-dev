const PimApp = require('pimui/js/pim-app');
const PimOnboarding = require('pim/free-trial/onboarding');

class FreeTrialPimApp extends PimApp {
  public configure() {
    return super.configure().then(() => {
        PimOnboarding.init();
      })
  }
}

export = FreeTrialPimApp;
