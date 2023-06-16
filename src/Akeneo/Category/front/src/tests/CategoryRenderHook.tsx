import React, {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {renderHook} from '@testing-library/react-hooks';
import {mockedDependencies, DependenciesContext} from '@akeneo-pim-community/shared';
import {MemoryRouter as Router} from 'react-router';

const categoryReactQueryProviders: FC = ({children}) => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        // by default, react query uses a back-off delay gradually applied to each retry attempt.
        // Overriding the default value allows us to test its failing behavior without slowing down
        // the tests.
        retryDelay: 10,
      },
    },
  });

  return (
    <QueryClientProvider client={queryClient}>
      <DependenciesContext.Provider value={mockedDependencies}>
        <ThemeProvider theme={pimTheme}>
          <Router>{children}</Router>
        </ThemeProvider>
      </DependenciesContext.Provider>
    </QueryClientProvider>
  );
};

const categoryRenderHookWithProviders = (hook: () => any) => renderHook(hook, {wrapper: categoryReactQueryProviders});

export {categoryRenderHookWithProviders, categoryReactQueryProviders};
