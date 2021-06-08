import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import {App} from './App';

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={{locale: 'en_US', messages: {}}}>
        <App/>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
