import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses, MockFetchResponses, renderWithProviders, historyMock} from '../../../../test-utils';
import {AppWizard} from '@src/connect/components/AppWizard/AppWizard';
import userEvent from '@testing-library/user-event';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {PermissionFormProvider, PermissionFormRegistryContext} from '@src/shared/permission-form-registry';
import {useFeatureFlags} from '@src/shared/feature-flags';
import {PermissionsByProviderKey} from '@src/model/Apps/permissions-by-provider-key';

jest.mock('@src/connect/components/AppWizard/steps/Authentication/Authentication', () => ({
    Authentication: () => <div>authentication-component</div>,
}));

jest.mock('@src/connect/components/AppWizard/steps/Authorizations', () => ({
    Authorizations: ({
        setScopesConsent,
        setCertificationConsent,
    }: {
        setScopesConsent: (newValue: boolean) => void;
        setCertificationConsent: (newValue: boolean) => void;
    }) => (
        <div>
            authorizations-component
            <div onClick={() => setScopesConsent(true)}>consent-scopes</div>
            <div onClick={() => setScopesConsent(false)}>unconsent-scopes</div>
            <div onClick={() => setCertificationConsent(true)}>consent-certification</div>
            <div onClick={() => setCertificationConsent(false)}>unconsent-certification</div>
        </div>
    ),
}));

jest.mock('akeneo-design-system', () => ({
    ...jest.requireActual('akeneo-design-system'),
    AppIllustration: () => <div>no-logo-illustration</div>,
}));

jest.mock('@src/shared/feature-flags/use-feature-flags');

type PermissionsProps = {
    appName: string;
    providers: PermissionFormProvider<any>[];
    setProviderPermissions: (providerKey: string, providerPermissions: object) => void;
    permissions: PermissionsByProviderKey;
};
jest.mock('@src/connect/components/AppWizard/steps/Permissions', () => ({
    ...jest.requireActual('@src/connect/components/AppWizard/steps/Permissions'),
    Permissions: ({permissions, setProviderPermissions}: PermissionsProps) => {
        const handleClick = () => {
            setProviderPermissions('data', {view: 'hello world!'});
            setProviderPermissions('formProvider1', {view: 'formProviderData1'});
            setProviderPermissions('formProvider2', {view: 'formProviderData2'});
            setProviderPermissions('formProvider3', {view: 'formProviderData3'});
            setProviderPermissions('formProvider4', {view: 'formProviderData4'});
        };

        return (
            <div data-testid='set-permissions' onClick={handleClick}>
                permissions-component {JSON.stringify(permissions.data)}
            </div>
        );
    },
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
const providerSave = jest.fn();

const setPermissionsAndConfirmWizard = async () => {
    await act(async () => userEvent.click(await screen.findByText('consent-scopes')));
    await act(async () => {
        userEvent.click(
            await screen.findByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
        );
    });

    await act(async () => userEvent.click(await screen.findByTestId('set-permissions')));
    await act(async () =>
        userEvent.click(await screen.findByText('akeneo_connectivity.connection.connect.apps.wizard.action.next'))
    );
    await act(async () =>
        userEvent.click(await screen.findByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'))
    );
};

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
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: false,
            }[feature] ?? false),
    }));

    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: false,
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
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: false,
            }[feature] ?? false),
    }));

    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: null,
                appIsCertified: false,
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
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: false,
            }[feature] ?? false),
    }));

    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: false,
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
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: false,
            }[feature] ?? false),
    }));

    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: false,
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
        userEvent.click(screen.getByText('consent-scopes'));
    });
    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });

    await waitFor(() => {
        expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.loader.message')).toBeInTheDocument();
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
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: false,
            }[feature] ?? false),
    }));

    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: false,
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

test('The wizard prevents going past the authorizations step without consent', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: false,
            }[feature] ?? false),
    }));

    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: false,
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

    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });

    expect(global.window.location.assign).not.toHaveBeenCalledWith('http://foo.example.com/oauth2/callback');
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('unconsent-scopes'));
    });
    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });

    expect(global.window.location.assign).not.toHaveBeenCalledWith('http://foo.example.com/oauth2/callback');
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('consent-scopes'));
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
    expect(global.window.location.assign).toHaveBeenCalledWith('http://foo.example.com/oauth2/callback');
});

test('The wizard prevents going past the authorizations step without certification consent', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: false,
            }[feature] ?? false),
    }));

    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: true,
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

    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
    act(() => {
        userEvent.click(screen.getByText('consent-scopes'));
    });

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });

    expect(global.window.location.assign).not.toHaveBeenCalledWith('http://foo.example.com/oauth2/callback');
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('unconsent-certification'));
    });
    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });

    expect(global.window.location.assign).not.toHaveBeenCalledWith('http://foo.example.com/oauth2/callback');
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('consent-certification'));
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
    expect(global.window.location.assign).toHaveBeenCalledWith('http://foo.example.com/oauth2/callback');
});

test('The wizard notifies an unspecified error occurred on app confirm', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: true,
            }[feature] ?? false),
    }));

    const clientId = '8d8a7dc1-0827-4cc9-9ae5-577c6419230b';
    const fetchAppWizardDataResponses: MockFetchResponses = {
        [`akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=${clientId}`]: {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: false,
                scopeMessages: [{entities: 'products', type: 'view', icon: 'products'}],
                oldScopeMessages: null,
                authenticationScopes: [],
                oldAuthenticationScopes: null,
            },
        },
        [`akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=${clientId}`]: {
            status: 500,
            json: '',
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <AppWizard clientId={clientId} />
        </NotifyContext.Provider>
    );
    await waitFor(() => screen.getByAltText('MyApp'));

    await setPermissionsAndConfirmWizard();

    await waitFor(() => expect(notify).toHaveBeenCalledTimes(1));

    expect(notify).toBeCalledWith(
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.error'
    );
});

test('The wizard saves app and permissions on confirm', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: true,
            }[feature] ?? false),
    }));

    const clientId = '8d8a7dc1-0827-4cc9-9ae5-577c6419230b';
    const appUserGroup = 'AppUserGroup';

    mockFetchResponses({
        [`akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=${clientId}`]: {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: false,
                scopeMessages: [{entities: 'products', type: 'view', icon: 'products'}],
                oldScopeMessages: null,
                authenticationScopes: [],
                oldAuthenticationScopes: null,
            },
        },
        [`akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=${clientId}`]: {
            json: {
                userGroup: appUserGroup,
                redirectUrl: 'http://foo.example.com/oauth2/callback',
            },
        },
    });

    const providers = [
        {
            key: 'formProvider1',
            label: 'formProviderLabel1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: providerSave,
            loadPermissions: jest.fn(),
        },
        {
            key: 'formProvider2',
            label: 'formProviderLabel2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: providerSave,
            loadPermissions: jest.fn(),
        },
    ];
    const registry = {
        all: () => Promise.resolve(providers),
    };

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <PermissionFormRegistryContext.Provider value={registry}>
                <AppWizard clientId={clientId} />
            </PermissionFormRegistryContext.Provider>
        </NotifyContext.Provider>
    );
    await waitFor(() => screen.getByAltText('MyApp'));

    await setPermissionsAndConfirmWizard();

    await waitFor(() => {
        expect(notify).toHaveBeenCalledTimes(1);
        expect(providerSave).toHaveBeenCalledTimes(2);
    });

    expect(notify).toBeCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.success'
    );

    expect(providerSave).toHaveBeenNthCalledWith(1, appUserGroup, {view: 'formProviderData1'});
    expect(providerSave).toHaveBeenNthCalledWith(2, appUserGroup, {view: 'formProviderData2'});

    expect(global.window.location.assign).toHaveBeenCalledWith('http://foo.example.com/oauth2/callback');
});

test('The wizard saves app but have some failing permissions on confirm', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                connect_app_with_permissions: true,
            }[feature] ?? false),
    }));

    const clientId = '8d8a7dc1-0827-4cc9-9ae5-577c6419230b';
    const appUserGroup = 'AppUserGroup';

    mockFetchResponses({
        [`akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=${clientId}`]: {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: false,
                scopeMessages: [{entities: 'products', type: 'view', icon: 'products'}],
                oldScopeMessages: null,
                authenticationScopes: [],
                oldAuthenticationScopes: null,
            },
        },
        [`akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=${clientId}`]: {
            json: {
                userGroup: appUserGroup,
            },
        },
    });

    providerSave
        .mockResolvedValue(undefined)
        .mockResolvedValueOnce(undefined)
        .mockResolvedValueOnce(undefined)
        .mockRejectedValueOnce(undefined)
        .mockImplementationOnce(() => {
            throw new Error('testError');
        });

    const providers = [
        {
            key: 'formProvider1',
            label: 'formProviderLabel1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: providerSave,
            loadPermissions: jest.fn(),
        },
        {
            key: 'formProvider2',
            label: 'formProviderLabel2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: providerSave,
            loadPermissions: jest.fn(),
        },
        {
            key: 'formProvider3',
            label: 'formProviderLabel3',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: providerSave,
            loadPermissions: jest.fn(),
        },
        {
            key: 'formProvider4',
            label: 'formProviderLabel4',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: providerSave,
            loadPermissions: jest.fn(),
        },
    ];
    const registry = {
        all: () => Promise.resolve(providers),
    };

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <PermissionFormRegistryContext.Provider value={registry}>
                <AppWizard clientId={clientId} />
            </PermissionFormRegistryContext.Provider>
        </NotifyContext.Provider>
    );
    await waitFor(() => screen.getByAltText('MyApp'));

    await setPermissionsAndConfirmWizard();

    await waitFor(() => {
        expect(notify).toHaveBeenCalledTimes(3);
        expect(providerSave).toHaveBeenCalledTimes(4);
    });

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.apps.flash.permissions_error.description',
        {
            titleMessage:
                'akeneo_connectivity.connection.connect.apps.flash.permissions_error.title?entity=formProviderLabel3',
        }
    );
    expect(notify).toHaveBeenNthCalledWith(
        2,
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.apps.flash.permissions_error.description',
        {
            titleMessage:
                'akeneo_connectivity.connection.connect.apps.flash.permissions_error.title?entity=formProviderLabel4',
        }
    );
    expect(notify).toHaveBeenNthCalledWith(
        3,
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.success'
    );

    expect(providerSave).toHaveBeenNthCalledWith(1, appUserGroup, {view: 'formProviderData1'});
    expect(providerSave).toHaveBeenNthCalledWith(2, appUserGroup, {view: 'formProviderData2'});
    expect(providerSave).toHaveBeenNthCalledWith(3, appUserGroup, {view: 'formProviderData3'});
    expect(providerSave).toHaveBeenNthCalledWith(4, appUserGroup, {view: 'formProviderData4'});
});

test('The wizard does not display permissions and confirm steps when there is no products scopes requested', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: 'http://example.com/logo.png',
                appIsCertified: false,
                scopeMessages: [],
                oldScopeMessages: null,
                authenticationScopes: [],
                oldAuthenticationScopes: null,
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });
    renderWithProviders(<AppWizard clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await screen.findByText('authorizations-component');

    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
    expect(screen.queryByText('permissions-component', {exact: false})).not.toBeInTheDocument();

    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.cancel')).toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')).toBeInTheDocument();

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.progress.authorizations')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).not.toBeInTheDocument();
});
