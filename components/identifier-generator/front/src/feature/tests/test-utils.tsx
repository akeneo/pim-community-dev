import React, {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {render, RenderOptions} from '@testing-library/react';

const AllTheProviders: FC<{children: React.ReactNode}> = ({children}) => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        // turns retries off because we don't need it inside tests
        retry: false,
      },
    },
  });

  const fakeRoutes = {
    pim_user_security_rest_get: {tokens: []}
  }

  return (
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={fakeRoutes as Routes}>
        <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  );
};

const customRender = (
  ui: React.ReactElement,
  options?: Omit<RenderOptions, 'wrapper'>
  // @ts-ignore
) => render(ui, {wrapper: AllTheProviders, ...options});

export * from '@testing-library/react';
export {customRender as render};
