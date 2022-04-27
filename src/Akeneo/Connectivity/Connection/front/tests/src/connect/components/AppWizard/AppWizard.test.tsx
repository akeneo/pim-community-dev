import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses, MockFetchResponses, renderWithProviders, historyMock} from '../../../../test-utils';
import {AppWizard} from '@src/connect/components/AppWizard/AppWizard';
import userEvent from '@testing-library/user-event';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';

const checkboxConsent = jest
    .fn(setScopesConsent => setScopesConsent(false))
    .mockImplementationOnce(setScopesConsent => setScopesConsent(false))
    .mockImplementationOnce(setScopesConsent => setScopesConsent(true));

jest.mock('@src/connect/components/AppWizard/steps/Authentication/Authentication', () => ({
    Authentication: ({setScopesConsent}: {setScopesConsent: (newValue: boolean) => void}) => (
        <div onClick={() => checkboxConsent(setScopesConsent)}>authentication-component</div>
    ),
}));

jest.mock('@src/connect/components/AppWizard/steps/Authorizations', () => ({
    Authorizations: () => <div>authorizations-component</div>,
}));

jest.mock('akeneo-design-system', () => ({
    ...jest.requireActual('akeneo-design-system'),
    AppIllustration: () => <div>no-logo-illustration</div>,
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

const notify = jest.fn();

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

test('The wizard renders without error', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                scopeMessages: [],
                authenticationScopes: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(<AppWizard clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitFor(() => screen.getByAltText('MyApp'));
    expect(screen.getByAltText('MyApp')).toBeInTheDocument();

    expect(screen.queryByText('authentication-component')).not.toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
});

test('The wizard renders without error when no logo', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: null,
                scopeMessages: [],
                authenticationScopes: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(<AppWizard clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    expect(await screen.findByText('no-logo-illustration')).toBeInTheDocument();

    expect(screen.queryByText('authentication-component')).not.toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
});

test('The wizard redirect to the marketplace when closed', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                scopeMessages: [],
                authenticationScopes: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(<AppWizard clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitFor(() => screen.getByAltText('MyApp'));
    expect(screen.getByAltText('MyApp')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByTitle('akeneo_connectivity.connection.connect.apps.wizard.action.cancel'));
    });

    expect(historyMock.history.location.pathname).toBe('/connect/app-store');
});

test('The wizard display a notification and redirects on success', async done => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                scopeMessages: [],
                authenticationScopes: [],
            },
        },
        'akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b':
            {
                json: {
                    userGroup: 'foo',
                    redirectUrl: 'http://foo.example.com/oauth2/callback',
                },
            },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <AppWizard clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />
        </NotifyContext.Provider>
    );
    await waitFor(() => screen.getByAltText('MyApp'));
    expect(screen.getByAltText('MyApp')).toBeInTheDocument();

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
    expect(global.window.location.assign).toHaveBeenCalledWith('http://foo.example.com/oauth2/callback');

    done();
});

test('The wizard display the authentication step', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                scopeMessages: [],
                authenticationScopes: ['profile'],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(<AppWizard clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitFor(() => screen.getByAltText('MyApp'));
    expect(screen.getByAltText('MyApp')).toBeInTheDocument();

    expect(screen.queryByText('authentication-component')).toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).not.toBeInTheDocument();
});

test('The wizard prevents going past the authentication step without consent', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                scopeMessages: [],
                authenticationScopes: ['profile'],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(<AppWizard clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitFor(() => screen.getByAltText('MyApp'));
    expect(screen.getByAltText('MyApp')).toBeInTheDocument();

    expect(screen.queryByText('authentication-component')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next'));
    });

    expect(screen.queryByText('authorizations-component')).not.toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('authentication-component'));
    });

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next'));
    });

    expect(screen.queryByText('authorizations-component')).not.toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('authentication-component'));
    });

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next'));
    });

    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
});
