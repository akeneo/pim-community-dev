import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import Index from './feature';
import {pimTheme} from 'akeneo-design-system';

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <Index jobCode="test" />
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
