import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {UserInterfaceHelper} from "./user-interface-helper";
import {QueryClient, QueryClientProvider} from "react-query";

class UserInterfaceHelperView extends ReactView {
    reactElementToMount() {
    const queryClient = new QueryClient();
    return (
      <DependenciesProvider>
        <QueryClientProvider client={queryClient}>
            <ThemeProvider theme={pimTheme}>
                <UserInterfaceHelper />
            </ThemeProvider>
        </QueryClientProvider>
      </DependenciesProvider>
    );
  }
}

export = UserInterfaceHelperView;
