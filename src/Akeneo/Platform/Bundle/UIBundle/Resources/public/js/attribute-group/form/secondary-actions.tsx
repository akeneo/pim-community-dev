import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {AttributeGroupSecondaryActions} from '@akeneo-pim-community/settings-ui';

class SecondaryActionsView extends ReactView {
  reactElementToMount() {
    const {code} = this.getRoot().getFormData();

    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupSecondaryActions attributeGroupCode={code} />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }
}

export = SecondaryActionsView;
