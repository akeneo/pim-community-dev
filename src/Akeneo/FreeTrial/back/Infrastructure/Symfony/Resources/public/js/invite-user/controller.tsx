import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {InviteUserApp} from '@akeneo-pim-community/invite-user';
import {PimInvitedUserProvider} from "./PimInvitedUserProvider";

const mediator = require('oro/mediator');

class InviteUserController extends ReactController {
  private static container = document.createElement('div');

  reactElementToMount() {
    return (
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <PimInvitedUserProvider>
              <InviteUserApp/>
          </PimInvitedUserProvider>
        </DependenciesProvider>
      </ThemeProvider>
    );
  }

  routeGuardToUnmount() {
    return /^akeneo_invite_user/;
  }

  getContainerRef(): Element {
    return InviteUserController.container;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: null});

    return super.renderRoute();
  }
}

export = InviteUserController;
