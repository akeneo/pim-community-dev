import {InviteUserButton} from 'akeneo-pim-free-trial';

const UserNavigation = require('pimui/js/menu/user-navigation');
const FeatureFlags = require('pim/feature-flags');

class FreeTrialUserNavigation extends UserNavigation {
  render() {
    if (!FeatureFlags.isEnabled('free_trial')) {
      return super.render();
    }

    super.render();

    this.renderReact(InviteUserButton, {}, this.el.querySelector('#invite-user-btn'));

    return this;
  }
}

export = FreeTrialUserNavigation;
