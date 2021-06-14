import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {InvitedUser, InvitedUserProvider, InviteUserApp} from '@akeneo-pim-community/invite-user';

class InviteUserController extends ReactController {
  private static container = document.createElement('div');

  private saveUsers = (emails: string[]):InvitedUser[] => {
    return emails.map((email:string) => {
      return {email, status: "invited"}
    })
  };
  private retrieveUsers = ():InvitedUser[] => {
    return []
  };

  reactElementToMount() {
    return (
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <InvitedUserProvider saveNewInvitedUsers={this.saveUsers} retrieveInvitedUsers={this.retrieveUsers}>
            <InviteUserApp/>
          </InvitedUserProvider>
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
