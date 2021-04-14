import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import Index from './feature';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesContext} from '@akeneo-pim-community/legacy-provider';

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
        <Index jobCode="test" />
      </DependenciesContext.Provider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
