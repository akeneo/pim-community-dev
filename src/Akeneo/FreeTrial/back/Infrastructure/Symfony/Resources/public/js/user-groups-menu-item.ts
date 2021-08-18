const MenuItem = require('pim/menu/item');
const FeatureFlags = require('pim/feature-flags');

class UserGroupsMenuItem extends MenuItem {
  initialize(config: any) {
    if (FeatureFlags.isEnabled('free_trial')) {
      config.config.disabled = true;
    }
    super.initialize(config);
  }
}

export = UserGroupsMenuItem;
