const BaseView = require('pim/common/simple-view');
const FeatureFlags = require('pim/feature-flags');

class UserProfileTabContent extends BaseView {
  public configure() {
    if (FeatureFlags.isEnabled('free_trial')) {
      Object.values(this.extensions).forEach((extension: any) => {
        if (extension.parent && [
          'pim-user-edit-form-general-tab-content',
          'pim-user-edit-form-password-tab-content',
          'pim-user-profile-form-general-tab-content',
          'pim-user-profile-form-password-tab-content'
        ].includes(extension.parent.code)) {
          extension.readOnly = true;
        }
      });
    }

    BaseView.prototype.configure.apply(this, arguments);
  }
}

export = UserProfileTabContent;
