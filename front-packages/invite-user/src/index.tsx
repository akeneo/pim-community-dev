import React from 'react';
import ReactDOM from 'react-dom';
import styled, {ThemeProvider} from 'styled-components';
import {CommonStyle, pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {InvitedUser, InviteUserApp, InviteUsersResponse, InvitedUserProvider} from './feature';

const Container = styled.div`
  ${CommonStyle}
`;

const saveUsers = async (emails: string[]): Promise<InviteUsersResponse> => {
  return {
    success: true,
    errors: [],
  };
};
const retrieveUsers = async (): Promise<InvitedUser[]> => {
  return [];
};

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      {/* @ts-ignore */}
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations}>
        <InvitedUserProvider saveNewInvitedUsers={saveUsers} retrieveInvitedUsers={retrieveUsers}>
          <Container>
            <InviteUserApp />
          </Container>
        </InvitedUserProvider>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
