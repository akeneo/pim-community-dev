import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {UserInterfaceHelper} from "./user-interface-helper";


class UserInterfaceHelperView extends ReactView {

    reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
            <UserInterfaceHelper />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }
}

export = UserInterfaceHelperView;
