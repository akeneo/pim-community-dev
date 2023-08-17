import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {historyMock, mockFetchResponses, MockFetchResponses, renderWithProviders} from '../../../../test-utils';
import {AppWizardWithPermissions} from '@src/connect/components/AppWizard/AppWizardWithPermissions';
import {PermissionFormProvider, PermissionFormRegistryContext} from '@src/shared/permission-form-registry';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {PermissionsByProviderKey} from '@src/model/Apps/permissions-by-provider-key';

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

jest.mock('@src/connect/components/AppWizard/steps/Authentication/Authentication', () => ({
    Authentication: () => <div>authentication-component</div>,
}));

jest.mock('@src/connect/components/AppWizard/steps/Authorizations', () => ({
    ...jest.requireActual('@src/connect/components/AppWizard/steps/Authorizations'),
    Authorizations: () => <div>authorizations-component</div>,
}));

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

type SummaryProps = {
    permissions: PermissionsByProviderKey;
};
jest.mock('@src/connect/components/AppWizard/steps/PermissionsSummary', () => ({
    ...jest.requireActual('@src/connect/components/AppWizard/steps/PermissionsSummary'),
    PermissionsSummary: ({permissions}: SummaryProps) => (
        <div>permissions-summary-component {JSON.stringify(permissions.data)}</div>
    ),
}));

test('The step wizard renders without error', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
                authenticationScopes: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });
    renderWithProviders(<AppWizardWithPermissions clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);

    await waitFor(() => screen.getByAltText('MyApp'));
    expect(screen.queryByAltText('MyApp')).toBeInTheDocument();
    expect(screen.queryByText('authentication-component')).not.toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
    expect(screen.queryByText('permissions-component')).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-summary-component')).not.toBeInTheDocument();
});

test('The wizard redirect to the marketplace when closed', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
                authenticationScopes: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(<AppWizardWithPermissions clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitFor(() => screen.getByAltText('MyApp'));

    act(() => {
        userEvent.click(screen.getByTitle('akeneo_connectivity.connection.connect.apps.wizard.action.cancel'));
    });

    expect(historyMock.history.location.pathname).toBe('/connect/app-store');
});

test('The wizard renders steps and is able to navigate between steps', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
                authenticationScopes: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });
    renderWithProviders(<AppWizardWithPermissions clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitFor(() => screen.getByAltText('MyApp'));

    assertAuthorizationsScreen();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next'));
    });
    assertPermissionsScreen();

    expect(screen.queryByText('permissions-component')).toBeInTheDocument();
    act(() => {
        userEvent.click(screen.getByTestId('set-permissions'));
    });

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.next'));
    });
    assertPermissionsSummaryScreen();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous'));
    });
    assertPermissionsScreen();
    expect(screen.queryByText('permissions-component {"view":"hello world!"}')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous'));
    });
    assertAuthorizationsScreen();
});

test('The wizard notifies of the error on app confirm ', async () => {
    const clientId = '8d8a7dc1-0827-4cc9-9ae5-577c6419230b';
    const fetchAppWizardDataResponses: MockFetchResponses = {
        [`akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=${clientId}`]: {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
                authenticationScopes: [],
            },
        },
        [`akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=${clientId}`]: {
            status: 400,
            json: '',
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <AppWizardWithPermissions clientId={clientId} />
        </NotifyContext.Provider>
    );

    await navigateToSummaryAndClickConfirm();

    await waitFor(() => {
        expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.loader.message')).toBeInTheDocument();
        expect(notify).toHaveBeenCalledTimes(1);
    });

    expect(notify).toBeCalledWith(
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.error'
    );
});

test('The wizard saves app and permissions on confirm', async () => {
    const clientId = '8d8a7dc1-0827-4cc9-9ae5-577c6419230b';
    const appUserGroup = 'AppUserGroup';

    mockFetchResponses({
        [`akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=${clientId}`]: {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
                authenticationScopes: [],
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
                <AppWizardWithPermissions clientId={clientId} />
            </PermissionFormRegistryContext.Provider>
        </NotifyContext.Provider>
    );

    await navigateToSummaryAndClickConfirm();

    await waitFor(() => {
        expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.loader.message')).toBeInTheDocument();
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
    const clientId = '8d8a7dc1-0827-4cc9-9ae5-577c6419230b';
    const appUserGroup = 'AppUserGroup';

    mockFetchResponses({
        [`akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=${clientId}`]: {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
                authenticationScopes: [],
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
                <AppWizardWithPermissions clientId={clientId} />
            </PermissionFormRegistryContext.Provider>
        </NotifyContext.Provider>
    );

    await navigateToSummaryAndClickConfirm();

    await waitFor(() => {
        expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.loader.message')).toBeInTheDocument();
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

test('The wizard display the authentication step', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
                authenticationScopes: ['profile'],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });
    renderWithProviders(<AppWizardWithPermissions clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);

    await waitFor(() => screen.getByAltText('MyApp'));
    expect(screen.queryByAltText('MyApp')).toBeInTheDocument();
    expect(screen.queryByText('authentication-component')).toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-component')).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-summary-component')).not.toBeInTheDocument();
});

const assertAuthorizationsScreen = () => {
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).not.toBeInTheDocument();

    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
    expect(screen.queryByText('permissions-component', {exact: false})).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-summary-component', {exact: false})).not.toBeInTheDocument();
};

const assertPermissionsScreen = () => {
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')
    ).not.toBeInTheDocument();

    expect(screen.queryByText('authorizations-component')).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-component', {exact: false})).toBeInTheDocument();
    expect(screen.queryByText('permissions-summary-component', {exact: false})).not.toBeInTheDocument();
};

const assertPermissionsSummaryScreen = () => {
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).toBeInTheDocument();

    expect(screen.queryByText('authorizations-component')).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-component', {exact: false})).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-summary-component', {exact: false})).toBeInTheDocument();
};

const navigateToSummaryAndClickConfirm = async () => {
    await waitFor(() => screen.getByAltText('MyApp'));

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next'));
    });

    expect(screen.queryByText('permissions-component')).toBeInTheDocument();
    act(() => {
        userEvent.click(screen.getByTestId('set-permissions'));
    });

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.next'));
    });
    assertPermissionsSummaryScreen();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });
};
