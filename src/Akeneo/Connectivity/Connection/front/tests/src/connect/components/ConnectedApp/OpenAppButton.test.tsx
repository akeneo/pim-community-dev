import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import {mockFetchResponses, MockFetchResponses, renderWithProviders} from '../../../../test-utils';
import usePermissionsFormProviders from '@src/connect/hooks/use-permissions-form-providers';
import {FlowType} from '@src/model/flow-type.enum';
import {SecurityContext} from '@src/shared/security';
import {OpenAppButton} from "@src/connect/components/ConnectedApp/OpenAppButton";
import fetchMock from "jest-fetch-mock";
import {ConnectedApp} from "@src/model/Apps/connected-app";
import {MonitoringSettings} from "@src/model/Apps/monitoring-settings";
import {PermissionFormProvider} from "@src/shared/permission-form-registry";
import {PermissionsByProviderKey} from "@src/model/Apps/permissions-by-provider-key";

beforeEach(() => {
    window.sessionStorage.clear();
    fetchMock.resetMocks();
    jest.clearAllMocks();
});

type ConnectedAppSettingsProps = {
    connectedApp: ConnectedApp;
    monitoringSettings: MonitoringSettings | null;
    handleSetMonitoringSettings: (monitoringSettings: MonitoringSettings) => void;
};
jest.mock('@src/connect/components/ConnectedApp/ConnectedAppSettings', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ConnectedAppSettings'),
    ConnectedAppSettings: jest.fn(({handleSetMonitoringSettings}: ConnectedAppSettingsProps) => {
        const handleClick = () => {
            handleSetMonitoringSettings({flowType: FlowType.DATA_DESTINATION, auditable: true});
        };

        return (
            <div data-testid='set-monitoring-settings' onClick={handleClick}>
                connected-app-monitoring-settings-form-component
            </div>
        );
    }),
}));

const saveConnectedAppMonitoringSettings = jest
    .fn()
    .mockImplementation((data: MonitoringSettings) => Promise.resolve());

jest.mock('@src/connect/hooks/use-save-connected-app-monitoring-settings.ts', () => ({
    ...jest.requireActual('@src/connect/hooks/use-save-connected-app-monitoring-settings.ts'),
    useSaveConnectedAppMonitoringSettings: jest
        .fn()
        .mockImplementation((connectionCode: string) => saveConnectedAppMonitoringSettings),
}));

jest.mock('@src/connect/components/ConnectedApp/ErrorMonitoring/ConnectedAppErrorMonitoring', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ErrorMonitoring/ConnectedAppErrorMonitoring'),
    ConnectedAppErrorMonitoring: jest.fn(() => null),
}));

jest.mock('@akeneo-pim-community/catalogs', () => ({
    CatalogList: jest.fn(() => null),
}));

type ConnectedAppPermissionsProps = {
    providers: PermissionFormProvider<any>[];
    setProviderPermissions: (providerKey: string, providerPermissions: object) => void;
    permissions: PermissionsByProviderKey;
    onlyDisplayViewPermissions: boolean;
};
jest.mock('@src/connect/components/ConnectedApp/ConnectedAppPermissions', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ConnectedAppPermissions'),
    ConnectedAppPermissions: jest.fn(({setProviderPermissions}: ConnectedAppPermissionsProps) => {
        const handleClick = () => {
            setProviderPermissions('providerKey1', {view: {all: true, identifiers: []}});
            setProviderPermissions('providerKey1bis', {view: {all: false, identifiers: ['code1bis']}});
            setProviderPermissions('providerKey2', {view: {all: false, identifiers: ['code2A', 'code2B']}});
        };

        return (
            <div data-testid='set-permissions' onClick={handleClick}>
                connected-app-permissions-tab-component
            </div>
        );
    }),
}));

jest.mock('@src/connect/hooks/use-permissions-form-providers', () => ({
    __esModule: true,
    default: jest.fn(() => [null, {}, jest.fn()]),
}));

const connectedApp = {
    id: '12345',
    name: 'App A',
    scopes: ['read_products', 'write_products'],
    connection_code: 'some_connection_code',
    logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
    author: 'Author A',
    user_group_name: 'app_123456abcde',
    connection_username: 'connection_username',
    categories: ['e-commerce', 'print'],
    certified: false,
    partner: null,
    is_test_app: false,
    is_pending: false,
    has_outdated_scopes: true,
};

test('The Open App button is disabled when the user doesnt have the permission to Open Apps', async () => {
    const isGranted = jest.fn(acl => {
        return acl !== 'akeneo_connectivity_connection_open_apps';
    });

    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <OpenAppButton connectedApp={connectedApp} />
        </SecurityContext.Provider>
    );

    const openAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    );
    openAppButton.not.toHaveAttribute('href');
    openAppButton.toHaveAttribute('disabled');
    openAppButton.toHaveAttribute('aria-disabled', 'true');
});

test('The Open App button is disabled for test app when the user doesnt have the permission to manage test apps', async () => {
    const isGranted = jest.fn(acl => {
        return acl !== 'akeneo_connectivity_connection_manage_test_apps';
    });

    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <OpenAppButton connectedApp={{...connectedApp, is_test_app: true}} />
        </SecurityContext.Provider>
    );

    const openAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    );
    openAppButton.not.toHaveAttribute('href');
    openAppButton.toHaveAttribute('disabled');
    openAppButton.toHaveAttribute('aria-disabled', 'true');
});

test('The Open App button is enabled when the user has the permission to manage apps', async () => {
    const isGranted = jest.fn(acl => {
        return acl !== 'akeneo_connectivity_connection_open_apps';
    });

    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <OpenAppButton connectedApp={connectedApp} />
        </SecurityContext.Provider>
    );

    const openAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    );
    openAppButton.toHaveAttribute(
        'href',
        '#akeneo_connectivity_connection_connect_connected_apps_open?connectionCode=some_connection_code'
    );
    openAppButton.not.toHaveAttribute('disabled');
    openAppButton.not.toHaveAttribute('aria-disabled', 'true');
});

test('The Open App button is enabled for test app when the user has the permission to manage test apps', async () => {
    const isGranted = jest.fn(acl => {
        return acl !== 'akeneo_connectivity_connection_manage_test_apps';
    });

    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <OpenAppButton connectedApp={{...connectedApp, is_test_app: true}} />
        </SecurityContext.Provider>
    );

    const openAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    );
    openAppButton.toHaveAttribute(
        'href',
        '#akeneo_connectivity_connection_connect_connected_apps_open?connectionCode=some_connection_code'
    );
    openAppButton.not.toHaveAttribute('disabled');
    openAppButton.not.toHaveAttribute('aria-disabled', 'true');
});

test('The Open App button is in warning state for test app when the connectedApp has a outdated scopes flag', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);

    renderWithProviders(<OpenAppButton connectedApp={{...connectedApp, has_outdated_scopes: true}} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    ).toHaveStyle('background-color: rgb(249, 181, 63)');
});
test('The Open App button is in warning state for test app when the connectedApp is pending', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);

    renderWithProviders(<OpenAppButton connectedApp={{...connectedApp, is_pending: true}} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    ).toHaveStyle('background-color: rgb(249, 181, 63)');
});
