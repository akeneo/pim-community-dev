import {UserContext} from '@src/shared/user';
import {render} from '@testing-library/react';
import React, {FC} from 'react';
import {create} from 'react-test-renderer';
import {ThemeProvider} from 'styled-components';
import {theme} from '@src/common/styled-with-theme';
import fetchMock from 'jest-fetch-mock';
import {Router} from 'react-router-dom';
import {createMemoryHistory} from 'history';
import {DependenciesContext} from '@akeneo-pim-community/shared';
import {QueryClientProvider, QueryClient} from 'react-query';

export const historyMock = {
    history: createMemoryHistory(),
    reset: () => {
        historyMock.history = createMemoryHistory();
    },
};

const UserProvider: FC = ({children}) => {
    const data: {[key: string]: unknown} = {
        uiLocale: 'en_US',
        timezone: 'UTC',
        avatar: {filePath: 'avatar.png'},
        first_name: 'John',
        last_name: 'Doe',
    };
    const user = {
        get: function <T>(key: string) {
            return data[key] as T;
        },
        set: () => undefined,
        refresh: () => Promise.resolve(),
    };

    return <UserContext.Provider value={user}>{children}</UserContext.Provider>;
};

export const ReactQueryWrapper: FC = ({children}) => {
    const queryClient = new QueryClient({
        defaultOptions: {
            queries: {
                // by default, react query uses a back-off delay gradually applied to each retry attempt.
                // Overriding the delay to 10ms allows us to test its failing behavior without slowing down
                // the tests.
                retryDelay: 10,
            },
        },
    });

    return <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>;
};
const DefaultProviders: FC = ({children}) => {
    return (
        <ReactQueryWrapper>
            <DependenciesContext.Provider
                value={{
                    translate: (id: string) => id,
                    featureFlags: {isEnabled: (_feature: string) => true},
                    systemConfiguration: {
                        get: (key: string) => key,
                        initialize: () => Promise.resolve(),
                        refresh: () => Promise.resolve(),
                    },
                }}
            >
                <ThemeProvider theme={theme}>
                    <UserProvider>
                        <Router history={historyMock.history}>{children}</Router>
                    </UserProvider>
                </ThemeProvider>
            </DependenciesContext.Provider>
        </ReactQueryWrapper>
    );
};

export const createWithProviders = (nextElement: React.ReactElement) =>
    create(<DefaultProviders>{nextElement}</DefaultProviders>);

export const renderWithProviders = (ui: React.ReactElement) => render(ui, {wrapper: DefaultProviders});

export const fetchMockResponseOnce = (requestUrl: string, responseBody: string) =>
    fetchMock.mockResponseOnce(request =>
        request.url === requestUrl ? Promise.resolve(responseBody) : Promise.reject()
    );

export type MockFetchResponses = {
    [url: string]: {
        reject?: boolean;
        status?: number;
        statusText?: string;
        headers?: string[][] | {[key: string]: string};
        json: object | string | boolean;
    };
};

export const mockFetchResponses = (responses: MockFetchResponses) => {
    fetchMock.doMock(request => {
        const response = responses[request.url];

        if (undefined === response) {
            throw Error('Fetch was called with a non mocked url: ' + request.url);
        }

        const {reject, json, ...params} = response;

        if (true === reject) {
            return Promise.reject();
        }

        return Promise.resolve({
            ...params,
            body: JSON.stringify(json),
        });
    });
};
