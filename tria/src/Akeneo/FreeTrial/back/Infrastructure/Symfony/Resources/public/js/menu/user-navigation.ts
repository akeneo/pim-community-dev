import {InviteUserButton} from 'akeneo-pim-free-trial';

const UserNavigation = require('pimui/js/menu/user-navigation');

class FreeTrialUserNavigation extends UserNavigation {
  render() {
    super.render();

    this.renderReact(
        InviteUserButton,
        {},
        document.getElementById('invite-user-btn')
    );

    return this;
  }
}

export = FreeTrialUserNavigation;
