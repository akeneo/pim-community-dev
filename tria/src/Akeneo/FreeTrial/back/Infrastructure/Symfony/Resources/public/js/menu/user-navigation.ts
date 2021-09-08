import {InviteUserButton} from 'akeneo-pim-free-trial';

const UserNavigation = require('pimui/js/menu/user-navigation');

class FreeTrialUserNavigation extends UserNavigation {

  render() {
    super.render();

    this.renderReact(
      InviteUserButton,
      {},
      this.el.querySelector('#invite-user-btn')
    );

    return this;
  }
}

export = FreeTrialUserNavigation;
