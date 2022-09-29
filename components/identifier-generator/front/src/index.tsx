import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  DangerousMicrofrontendAutomaticAuthenticator,
  MicroFrontendDependenciesProvider,
  Routes,
} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import {IdentifierGeneratorApp} from './feature';

DangerousMicrofrontendAutomaticAuthenticator.enable('admin', 'admin');

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes}>
        <IdentifierGeneratorApp />
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
