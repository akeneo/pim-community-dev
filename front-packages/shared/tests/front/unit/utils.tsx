import React, {FC} from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {renderHook} from '@testing-library/react-hooks';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesContext} from '../../../src/DependenciesContext';
import {dependencies} from './dependencies';

const DefaultProviders: FC = ({children}) => (
  <DependenciesContext.Provider value={dependencies}>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesContext.Provider>
);

const renderWithProviders = (ui: React.ReactElement) => render(ui, {wrapper: DefaultProviders});

const renderDOMWithProviders = (ui: React.ReactElement, container: HTMLElement) =>
  ReactDOM.render(<DefaultProviders>{ui}</DefaultProviders>, container);

const renderHookWithProviders = (hook: () => any) => renderHook(hook, {wrapper: DefaultProviders});

const fetchMockResponseOnce = (requestUrl: string, responseBody: string) =>
  fetchMock.mockResponseOnce(request =>
    request.url === requestUrl ? Promise.resolve(responseBody) : Promise.reject()
  );

export {renderWithProviders, renderDOMWithProviders, renderHookWithProviders, fetchMockResponseOnce};
