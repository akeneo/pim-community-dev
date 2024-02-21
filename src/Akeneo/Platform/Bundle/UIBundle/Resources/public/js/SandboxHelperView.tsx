import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {SandboxHelper} from '@akeneo-pim-community/shared';

class SandboxHelperView extends ReactView {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <SandboxHelper />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }
}

export = SandboxHelperView;
