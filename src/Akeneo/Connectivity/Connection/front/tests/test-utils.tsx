import {UserContext} from '@src/shared/user';
import {render} from '@testing-library/react';
import React, {FC} from 'react';
import {create} from 'react-test-renderer';
import {ThemeProvider} from 'styled-components';
import {theme} from '../src/common/styled-with-theme';

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
