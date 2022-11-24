import React, {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesContext} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {render, RenderOptions, RenderResult} from '@testing-library/react';
import {IdentifierGeneratorContextProvider} from '../context';

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
      <DependenciesContext.Provider value={{translate: k => k}}>
        <QueryClientProvider client={queryClient}>
          <IdentifierGeneratorContextProvider>{children}</IdentifierGeneratorContextProvider>
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

const mockResponse: (
  url: string,
  method: string,
  response: {ok?: boolean; json?: unknown; statusText?: string; status?: number; body?: unknown}
) => () => void = (url, method, response) => {
  if (!response.ok) {
    jest.spyOn(console, 'error');
    // eslint-disable-next-line no-console
    (console.error as jest.Mock).mockImplementation(() => null);
  }
  const fetchImplementation = jest.fn().mockImplementation((requestUrl: string, args: {method: string}) => {
    if (requestUrl === url && args.method === method) {
      return Promise.resolve({
        ok: response.ok ?? true,
        json: () => Promise.resolve(response.json || {}),
        statusText: response.statusText || '',
        status: response.status ?? 200,
      } as Response);
    }

    throw new Error(`Unmocked url "${requestUrl}" [${args.method}]`);
  });
  jest.spyOn(global, 'fetch').mockImplementation(fetchImplementation);

  return () => {
    if (method === 'POST') {
      expect(fetchImplementation).toBeCalledWith(url, {
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json'},
        body: JSON.stringify(response.body),
        method: 'POST',
      });
    } else {
      expect(fetchImplementation).toBeCalledWith(url, {
        headers: [['X-Requested-With', 'XMLHttpRequest']],
        method: method,
      });
    }
  };
};

export * from '@testing-library/react';
export {customRender as render, mockResponse};
