import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes, Translations} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {FakePIM} from './FakePIM';
import {QueryClient, QueryClientProvider} from 'react-query';

const queryClient = new QueryClient();

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <QueryClientProvider client={queryClient}>
        <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
          <FakePIM />
        </MicroFrontendDependenciesProvider>
      </QueryClientProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
