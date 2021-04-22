import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {Column} from './feature';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider} from './MicroFrontendDependenciesProvider';

ReactDOM.render(
  <React.StrictMode>
    <MicroFrontendDependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <Column jobCode="test" />
      </ThemeProvider>
    </MicroFrontendDependenciesProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
