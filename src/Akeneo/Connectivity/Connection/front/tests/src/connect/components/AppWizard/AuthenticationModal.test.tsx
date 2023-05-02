import {AuthenticationModal} from '@src/connect/components/AppWizard/AuthenticationModal';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import '@testing-library/jest-dom/extend-expect';
import {act, render, screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {pimTheme} from 'akeneo-design-system';
import React from 'react';
import {ThemeProvider} from 'styled-components';
import {historyMock, mockFetchResponses, renderWithProviders} from '../../../../test-utils';
import {Authentication} from '@src/connect/components/AppWizard/steps/Authentication/Authentication';

const notify = jest.fn();
const checkboxConsent = jest.fn(setScopesConsent => setScopesConsent(true));

jest.mock('@src/connect/components/AppWizard/steps/Authentication/Authentication', () => ({
    Authentication: jest.fn(({setScopesConsent}: {setScopesConsent: (newValue: boolean) => void}) => (
        <div onClick={() => checkboxConsent(setScopesConsent)}>authentication-component</div>
    )),
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
    global.window =  {
        location: {
            assign: jest.fn(),
        }
    };
});

test('it renders correctly', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=0dfce574-2238-4b13-b8cc-8d257ce7645b': {
            json: {
                appName: 'Extension 1',
                appLogo: 'https://extension-1.test/logo.png',
                appUrl: 'https://myapp.example.com',
                scopeMessages: [],
                oldScopeMessages: null,
                authenticationScopes: ['email', 'profile'],
                oldAuthenticationScopes: null,
            },
        },
    });

    renderWithProviders(<AuthenticationModal clientId='0dfce574-2238-4b13-b8cc-8d257ce7645b' />);

    await waitFor(() => screen.queryByText('authentication-component'));

    expect(screen.queryByText('authentication-component')).toBeInTheDocument();
    expect(Authentication).toBeCalledWith(
        expect.objectContaining({
            appName: 'Extension 1',
            scopes: ['email', 'profile'],
            oldScopes: null,
            appUrl: 'https://myapp.example.com',
            scopesConsentGiven: false,
            displayConsent: true,
        }),
        {}
    );
});

test('it consents to the authentication scopes & redirect the user', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=0dfce574-2238-4b13-b8cc-8d257ce7645b': {
            json: {
                appName: 'Extension 1',
                appLogo: 'https://extension-1.test/logo.png',
                scopeMessages: [],
                oldScopeMessages: null,
                authenticationScopes: [],
                oldAuthenticationScopes: null,
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
                <AuthenticationModal clientId='0dfce574-2238-4b13-b8cc-8d257ce7645b' />
            </NotifyContext.Provider>
        </ThemeProvider>
    );

    await waitFor(() => screen.queryByText('authentication-component'));

    act(() => {
        userEvent.click(screen.getByText('authentication-component'));
    });

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
    historyMock.history.push('/');

    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=0dfce574-2238-4b13-b8cc-8d257ce7645b': {
            json: {
                appName: 'Extension 1',
                appLogo: 'https://extension-1.test/logo.png',
                scopeMessages: [],
                oldScopeMessages: null,
                authenticationScopes: [],
                oldAuthenticationScopes: null,
            },
        },
    });

    renderWithProviders(<AuthenticationModal clientId='0dfce574-2238-4b13-b8cc-8d257ce7645b' />);

    await waitFor(() => screen.queryByText('authentication-component'));

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.cancel'));
    });

    expect(historyMock.history.location.pathname).toBe('/connect/connected-apps');
});

test('it prevents redirection without user consent', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=0dfce574-2238-4b13-b8cc-8d257ce7645b': {
            json: {
                appName: 'Extension 1',
                appLogo: 'https://extension-1.test/logo.png',
                scopeMessages: [],
                oldScopeMessages: null,
                authenticationScopes: [],
                oldAuthenticationScopes: null,
                displayCheckboxConsent: true,
            },
        },
        'akeneo_connectivity_connection_apps_rest_confirm_authentication?clientId=0dfce574-2238-4b13-b8cc-8d257ce7645b':
            {
                json: {
                    redirectUrl: 'https://extension-1.test/callback',
                },
            },
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <AuthenticationModal clientId='0dfce574-2238-4b13-b8cc-8d257ce7645b' />
        </NotifyContext.Provider>
    );

    await waitFor(() => screen.queryByText('authentication-component'));

    expect(screen.queryByText('authentication-component')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });

    expect(notify).not.toBeCalled();

    act(() => {
        userEvent.click(screen.getByText('authentication-component'));
    });

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });

    await waitFor(() => expect(notify).toHaveBeenCalledTimes(1));

    expect(notify).toBeCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.success'
    );
});

test('it allows redirection when checkbox consent is not displayed', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=0dfce574-2238-4b13-b8cc-8d257ce7645b': {
            json: {
                appName: 'Extension 1',
                appLogo: 'https://extension-1.test/logo.png',
                scopeMessages: [],
                oldScopeMessages: null,
                authenticationScopes: [],
                oldAuthenticationScopes: null,
                displayCheckboxConsent: false,
            },
        },
        'akeneo_connectivity_connection_apps_rest_confirm_authentication?clientId=0dfce574-2238-4b13-b8cc-8d257ce7645b':
            {
                json: {
                    redirectUrl: 'https://extension-1.test/callback',
                },
            },
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <AuthenticationModal clientId='0dfce574-2238-4b13-b8cc-8d257ce7645b' />
        </NotifyContext.Provider>
    );

    await waitFor(() => screen.queryByText('authentication-component'));

    expect(screen.queryByText('authentication-component')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });

    await waitFor(() => expect(notify).toHaveBeenCalledTimes(1));

    expect(notify).toBeCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.success'
    );
});
