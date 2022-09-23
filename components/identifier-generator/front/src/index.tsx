import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  DangerousMicrofrontendAutomaticAuthenticator,
  MicroFrontendDependenciesProvider,
  Routes,
  Translations,
} from '@akeneo-pim-community/shared';
import {QueryClient, QueryClientProvider} from 'react-query';
import {routes} from './routes.json';
import translations from './translations.json';
import {IdentifierGeneratorApp} from './feature';

DangerousMicrofrontendAutomaticAuthenticator.enable('admin', 'admin');

// Create a client
const queryClient = new QueryClient();

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <QueryClientProvider client={queryClient}>
        <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
          <IdentifierGeneratorApp />
        </MicroFrontendDependenciesProvider>
      </QueryClientProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
