import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {DuplicateOption} from './DuplicateOption';

class DuplicateSecondaryAction extends ReactView {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <DuplicateOption userId={this.getFormData().meta.id} />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }
}

export = DuplicateSecondaryAction;
