const BaseView = require('pim/common/simple-view');
const FeatureFlags = require('pim/feature-flags');

class UserProfileTabContent extends BaseView {
  public configure() {
    if (FeatureFlags.isEnabled('free_trial')) {
      Object.values(this.extensions).forEach((extension: any) => {
        extension.readOnly = true;
      });
    }

    BaseView.prototype.configure.apply(this, arguments);
  }
}

export = UserProfileTabContent;
