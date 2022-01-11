import {AuthenticationModal} from '@src/connect/components/AppWizard/AuthenticationModal';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import '@testing-library/jest-dom/extend-expect';
import {act, render, screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {pimTheme} from 'akeneo-design-system';
import React from 'react';
import {ThemeProvider} from 'styled-components';
import {historyMock, mockFetchResponses} from '../../../../test-utils';
import {createMemoryHistory} from 'history';
import {Router} from 'react-router-dom';

jest.mock('@src/connect/components/AppWizard/steps/Authentication/Authentication', () => ({
    Authentication: () => <div>authentication-component</div>,
}));

/*eslint-disable */
declare global {
    namespace NodeJS {
        interface Global {
            window: any;
        }
    }
}
/*eslint-enable */

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();

    delete global.window.location;
    global.window = Object.create(window);
    global.window.location = {
        assign: jest.fn(),
    };
});

test('it renders correctly', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            json: {
                total: 1,
                apps: [
                    {
                        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                        name: 'Extension 1',
                        logo: 'https://extension-1.test/logo.png',
                    },
                ],
            },
        },
    });

    render(
        <ThemeProvider theme={pimTheme}>
            <AuthenticationModal clientId='0dfce574-2238-4b13-b8cc-8d257ce7645b' newAuthenticationScopes={[]} />
        </ThemeProvider>
    );

    await waitFor(() => screen.queryByText('authentication-component'));

    expect(screen.queryByText('authentication-component')).toBeInTheDocument();
});

test('it consents to the authentication scopes & redirect the user', async () => {
    const notify = jest.fn();

    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            json: {
                total: 1,
                apps: [
                    {
                        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                        name: 'Extension 1',
                        logo: 'https://extension-1.test/logo.png',
                    },
                ],
            },
        },
        'akeneo_connectivity_connection_apps_rest_confirm_authentication?clientId=0dfce574-2238-4b13-b8cc-8d257ce7645b':
            {
                json: {
                    redirectUrl: 'https://extension-1.test/callback',
                },
            },
    });

    render(
        <ThemeProvider theme={pimTheme}>
            <NotifyContext.Provider value={notify}>
                <AuthenticationModal clientId='0dfce574-2238-4b13-b8cc-8d257ce7645b' newAuthenticationScopes={[]} />
            </NotifyContext.Provider>
        </ThemeProvider>
    );

    await waitFor(() => screen.queryByText('authentication-component'));

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });

    await waitFor(() => {
        expect(notify).toHaveBeenCalledTimes(1);
    });

    expect(notify).toBeCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.success'
    );

    expect(global.window.location.assign).toHaveBeenCalledWith('https://extension-1.test/callback');
});

test('it cancels the authentication', async () => {
    const history = createMemoryHistory({initialEntries: ['/']});

    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            json: {
                total: 1,
                apps: [
                    {
                        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                        name: 'Extension 1',
                        logo: 'https://extension-1.test/logo.png',
                    },
                ],
            },
        },
    });

    render(
        <ThemeProvider theme={pimTheme}>
            <Router history={history}>
                <AuthenticationModal clientId='0dfce574-2238-4b13-b8cc-8d257ce7645b' newAuthenticationScopes={[]} />
            </Router>
        </ThemeProvider>
    );

    await waitFor(() => screen.queryByText('authentication-component'));

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.cancel'));
    });

    expect(history.location.pathname).toBe('/connect/connected-apps');
});
