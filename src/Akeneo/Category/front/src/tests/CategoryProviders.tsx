import {DependenciesContext, mockedDependencies} from '@akeneo-pim-community/shared';
import {pimTheme} from 'akeneo-design-system';
import {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {MemoryRouter as Router} from 'react-router';
import {ThemeProvider} from 'styled-components';

export const CategoryProviders: FC = ({children}) => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
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
