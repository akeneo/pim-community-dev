import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {CreateButton} from './create/CreateButton';

class CreateButtonController extends ReactView {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CreateButton />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }
}

export = CreateButtonController;
