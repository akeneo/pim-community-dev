import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {InviteUserApp} from './InviteUserApp';

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
        {/* @ts-ignore */}
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations}>
        <InviteUserApp/>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
