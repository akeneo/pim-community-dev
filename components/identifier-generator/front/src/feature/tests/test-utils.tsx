import React, {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes, Translations} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
// @ts-ignore
import {routes} from '../../routes.json';
import {render, RenderOptions} from '@testing-library/react';

const AllTheProviders: FC<{children: React.ReactNode}> = ({children}) => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        // turns retries off because we dont need it inside tests
        retry: false,
      },
    },
  });

  return (
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={{messages: {}} as Translations}>
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
