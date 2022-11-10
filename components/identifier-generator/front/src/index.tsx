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
import {IdentifierGeneratorContext} from './feature/context/IdentifierGeneratorContext';

DangerousMicrofrontendAutomaticAuthenticator.enable('admin', 'admin');

const value = {
  unsavedChanges: {
    hasUnsavedChanges: false,
    // eslint-disable-next-line @typescript-eslint/no-empty-function
    setHasUnsavedChanges: (hasChanges: boolean) => (value.unsavedChanges.hasUnsavedChanges = hasChanges),
  },
};

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes}>
        <IdentifierGeneratorContext.Provider value={value}>
          <IdentifierGeneratorApp />
        </IdentifierGeneratorContext.Provider>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
