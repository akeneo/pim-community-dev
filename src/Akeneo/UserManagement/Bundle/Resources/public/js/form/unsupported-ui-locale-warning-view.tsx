import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {UnsupportedUiLocaleWarning} from './unsupported-ui-locale-warning';
import {QueryClient, QueryClientProvider} from 'react-query';

class UnsupportedUiLocaleWarningView extends ReactView {
  reactElementToMount() {
    const queryClient = new QueryClient();
    return (
      <DependenciesProvider>
        <QueryClientProvider client={queryClient}>
          <ThemeProvider theme={pimTheme}>
            <UnsupportedUiLocaleWarning />
          </ThemeProvider>
        </QueryClientProvider>
      </DependenciesProvider>
    );
  }
}

export = UnsupportedUiLocaleWarningView;
