import React, {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesContext, useSecurity} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {render, RenderOptions, RenderResult} from '@testing-library/react';
import {IdentifierGeneratorAclContextProvider, IdentifierGeneratorContextProvider} from '../context';

// By default, the tests should behave as if all identifier generator ACLs are granted
const AllTheProviders: FC<{children: React.ReactNode}> = ({children}) => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        // turns retries off because we don't need it inside tests
        retry: false,
      },
    },
  });

  return (
    <ThemeProvider theme={pimTheme}>
      <DependenciesContext.Provider
        value={{translate: k => k, security: {isGranted: () => true}, featureFlags: {isEnabled: () => true}}}
      >
        <QueryClientProvider client={queryClient}>
          <IdentifierGeneratorContextProvider>
            <IdentifierGeneratorAclContextProvider>{children}</IdentifierGeneratorAclContextProvider>
          </IdentifierGeneratorContextProvider>
        </QueryClientProvider>
      </DependenciesContext.Provider>
    </ThemeProvider>
  );
};

const customRender = (
  ui: React.ReactElement,
  options?: Omit<RenderOptions, 'wrapper'>
  // @ts-ignore
): RenderResult => render(ui, {wrapper: AllTheProviders, ...options});

const mockACLs: (view: boolean, manage: boolean) => void = (view: boolean, manage: boolean) => {
  (useSecurity as jest.Mock).mockImplementation(() => ({
    isGranted: (acl: string) =>
      ({
        pim_identifier_generator_view: view,
        pim_identifier_generator_manage: manage,
      }[acl] ?? false),
  }));
};

export * from '@testing-library/react';
export {customRender as render, mockACLs};
