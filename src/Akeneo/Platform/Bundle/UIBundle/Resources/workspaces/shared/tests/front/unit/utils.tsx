import ReactDOM from 'react-dom';
import React, {FC} from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const DefaultProviders: FC = ({children}) => {
  return (
    <DependenciesProvider>
      <AkeneoThemeProvider>
        {children}
      </AkeneoThemeProvider>
    </DependenciesProvider>
  );
};

export const createWithProviders = (nextElement: React.ReactElement) => <DefaultProviders>{nextElement}</DefaultProviders>;

export const renderWithProviders = (ui: React.ReactElement, container: HTMLElement) => ReactDOM.render(createWithProviders(ui), container);

export const renderHookWithProviders = (hook: () => any) => renderHook(() => hook(), {wrapper: ({children}) => <DefaultProviders>{children}</DefaultProviders>});

export const fetchMockResponseOnce = (requestUrl: string, responseBody: string) =>
    fetchMock.mockResponseOnce(request =>
        request.url === requestUrl ? Promise.resolve(responseBody) : Promise.reject()
    );
