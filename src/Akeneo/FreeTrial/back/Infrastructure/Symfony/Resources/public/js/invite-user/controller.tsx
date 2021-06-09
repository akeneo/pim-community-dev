import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {InviteUserApp} from '@akeneo-pim-community/invite-user';

class InviteUserController extends ReactController {
  private static container = document.createElement('div');

  reactElementToMount() {
    return (
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <InviteUserApp/>
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
}

export = InviteUserController;
