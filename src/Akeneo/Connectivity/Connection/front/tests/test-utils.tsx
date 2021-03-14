import {UserContext} from '@src/shared/user';
import {render} from '@testing-library/react';
import React, {FC} from 'react';
import {create} from 'react-test-renderer';
import {ThemeProvider} from 'styled-components';
import {theme} from '../src/common/styled-with-theme';
import fetchMock from 'jest-fetch-mock';

const UserProvider: FC = ({children}) => {
    const data: {[key: string]: string} = {uiLocale: 'en_US', timezone: 'UTC'};
    const user = {
        get: (key: string) => data[key],
        set: () => undefined,
    };

    return <UserContext.Provider value={user}>{children}</UserContext.Provider>;
};

const DefaultProviders: FC = ({children}) => {
    return (
        <ThemeProvider theme={theme}>
            <UserProvider>{children}</UserProvider>
        </ThemeProvider>
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
        reject?: boolean,
        status?: number;
        statusText?: string;
        headers?: string[][]|{[key: string]: string};
        json: object,
    },
};

export const mockFetchResponses = (responses: MockFetchResponses) => {
    fetchMock.doMock(request => {
        const response = responses[request.url];

        if (undefined === response) {
            throw Error('Fetch was called with a non mocked url: ' + request.url);
        }

        const { reject, json, ...params } = response;

        if (true === reject) {
            return Promise.reject();
        }

        return Promise.resolve({
            ...params,
            body: JSON.stringify(json),
        });
    });
};
