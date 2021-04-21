import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {Column} from './feature';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesContext} from '@akeneo-pim-community/shared';

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <DependenciesContext.Provider
        value={{
          translate: (id: string): string => {
            // @ts-ignore
            return id;
          },
        }}
      >
        <Column jobCode="test" />
      </DependenciesContext.Provider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
