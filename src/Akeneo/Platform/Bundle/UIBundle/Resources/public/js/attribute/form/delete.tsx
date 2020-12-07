import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {DeleteAction} from './delete/DeleteAction';

class Delete extends ReactView {
  reactElementToMount() {
    const data = this.getRoot().getFormData();

    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <DeleteAction attributeCode={data.code}/>
        </ThemeProvider>
      </DependenciesProvider>
    );
  }
}

export = Delete;
