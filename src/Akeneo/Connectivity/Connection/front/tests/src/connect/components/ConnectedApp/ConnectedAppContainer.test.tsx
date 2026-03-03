import React, {useState} from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitFor} from '@testing-library/react';
import {mockFetchResponses, MockFetchResponses, renderWithProviders} from '../../../../test-utils';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';
import {ConnectedAppSettings} from '@src/connect/components/ConnectedApp/ConnectedAppSettings';
import {ConnectedAppPermissions} from '@src/connect/components/ConnectedApp/ConnectedAppPermissions';
import userEvent from '@testing-library/user-event';
import usePermissionsFormProviders from '@src/connect/hooks/use-permissions-form-providers';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {PermissionFormProvider} from '@src/shared/permission-form-registry';
import {PermissionsByProviderKey} from '@src/model/Apps/permissions-by-provider-key';
import {FlowType} from '@src/model/flow-type.enum';
import fetchMock from 'jest-fetch-mock';
import {useSaveConnectedAppMonitoringSettings} from '@src/connect/hooks/use-save-connected-app-monitoring-settings';
import {ConnectedApp} from '@src/model/Apps/connected-app';
import {MonitoringSettings} from '@src/model/Apps/monitoring-settings';
import {ConnectedAppErrorMonitoring} from '@src/connect/components/ConnectedApp/ErrorMonitoring/ConnectedAppErrorMonitoring';
import {CatalogList} from '@akeneo-pim-community/catalogs';

beforeEach(() => {
    window.sessionStorage.clear();
    fetchMock.resetMocks();
    jest.clearAllMocks();
});

// to make Tab usable with jest
type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;
let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
    observe: jest.fn(() => (entryCallback = callback)),
    unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

const notify = jest.fn();

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
    is_custom_app: false,
    is_pending: false,
    has_outdated_scopes: true,
};

test('The connected app container renders without permissions tab', async () => {
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

    renderWithProviders(<ConnectedAppContainer connectedApp={connectedApp} />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings'));

    assertPageHeader();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.error_monitoring')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
    ).not.toBeInTheDocument();
    expect(ConnectedAppSettings).toHaveBeenCalledWith(
        expect.objectContaining({
            connectedApp: connectedApp,
        }),
        {}
    );
    expect(ConnectedAppPermissions).not.toHaveBeenCalled();
    expect(ConnectedAppErrorMonitoring).not.toHaveBeenCalled();
});

test('The connected app container renders the error monitoring', () => {
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

    renderWithProviders(<ConnectedAppContainer connectedApp={connectedApp} />);

    assertPageHeader();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.error_monitoring')
    ).toBeInTheDocument();
    expect(ConnectedAppErrorMonitoring).not.toHaveBeenCalled();

    act(() => {
        userEvent.click(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.error_monitoring')
        );
    });
    expect(ConnectedAppErrorMonitoring).toHaveBeenCalledWith({connectedApp: connectedApp}, {});
});

test('The connected app container renders with permissions tab', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];
    const mockedPermissions = {
        providerKey1: {
            view: {
                all: true,
                identifiers: [],
            },
        },
        providerKey2: {
            view: {
                all: false,
                identifiers: ['codeA'],
            },
        },
    };

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [
        mockedProviders,
        mockedPermissions,
        jest.fn(),
    ]);

    renderWithProviders(<ConnectedAppContainer connectedApp={connectedApp} />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings'));

    assertPageHeader();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
    ).toBeInTheDocument();
    expect(ConnectedAppSettings).toHaveBeenCalledWith(
        expect.objectContaining({
            connectedApp: connectedApp,
        }),
        {}
    );
    expect(ConnectedAppPermissions).not.toHaveBeenCalled();

    act(() => {
        userEvent.click(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
        );
    });

    expect(ConnectedAppPermissions).toHaveBeenCalledWith(
        expect.objectContaining({
            providers: mockedProviders,
            permissions: mockedPermissions,
            onlyDisplayViewPermissions: false,
        }),
        {}
    );
});

test('The connected app container renders the permissions tab with the view only option', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];
    const mockedPermissions = {
        providerKey1: {
            view: {
                all: true,
                identifiers: [],
            },
        },
        providerKey2: {
            view: {
                all: false,
                identifiers: ['codeA'],
            },
        },
    };

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [
        mockedProviders,
        mockedPermissions,
        jest.fn(),
    ]);

    renderWithProviders(<ConnectedAppContainer connectedApp={{...connectedApp, scopes: ['read_products']}} />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings'));

    assertPageHeader();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
    ).toBeInTheDocument();
    expect(ConnectedAppSettings).toHaveBeenCalledWith(
        expect.objectContaining({
            connectedApp: {...connectedApp, scopes: ['read_products']},
        }),
        {}
    );
    expect(ConnectedAppPermissions).not.toHaveBeenCalled();

    act(() => {
        userEvent.click(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
        );
    });

    expect(ConnectedAppPermissions).toHaveBeenCalledWith(
        expect.objectContaining({
            providers: mockedProviders,
            permissions: mockedPermissions,
            onlyDisplayViewPermissions: true,
        }),
        {}
    );
});

test('The connected app container does not render with permissions tab if there is no scope with product permission', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];
    const mockedPermissions = {
        providerKey1: {
            view: {
                all: true,
                identifiers: [],
            },
        },
        providerKey2: {
            view: {
                all: false,
                identifiers: ['codeA'],
            },
        },
    };

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [
        mockedProviders,
        mockedPermissions,
        jest.fn(),
    ]);

    renderWithProviders(<ConnectedAppContainer connectedApp={{...connectedApp, scopes: ['read_catalog_structure']}} />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings'));

    assertPageHeader();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
    ).not.toBeInTheDocument();
});

test('The connected app container renders the catalogs tab with the view only option', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    renderWithProviders(<ConnectedAppContainer connectedApp={{...connectedApp, scopes: ['read_catalogs']}} />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings'));

    assertPageHeader();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.catalogs')
    ).toBeInTheDocument();
    expect(ConnectedAppSettings).toHaveBeenCalledWith(
        expect.objectContaining({
            connectedApp: {...connectedApp, scopes: ['read_catalogs']},
        }),
        {}
    );

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.catalogs'));
    });

    expect(CatalogList).toHaveBeenCalledWith(
        expect.objectContaining({
            owner: 'connection_username',
        }),
        {}
    );
});

test('The connected app container does not render with catalogs tab if there is no scope with catalog permission', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    renderWithProviders(<ConnectedAppContainer connectedApp={{...connectedApp, scopes: ['read_catalog_structure']}} />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings'));

    assertPageHeader();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.catalogs')
    ).not.toBeInTheDocument();
});

test('The connected app container saves permissions', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => {
        const [permissions, setPermissions] = useState({});

        return [mockedProviders, permissions, setPermissions];
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <ConnectedAppContainer connectedApp={connectedApp} />
        </NotifyContext.Provider>
    );

    await waitFor(() => {
        expect(ConnectedAppSettings).toHaveBeenCalled();
    });

    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();

    navigateToPermissionsAndFillTheFormAndSave();

    await waitFor(() => {
        expect(notify).toHaveBeenCalledTimes(1);
    });

    expect(notify).toHaveBeenCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.connected_apps.edit.flash.success'
    );
    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();
    expect(mockedProviders[0].save).toHaveBeenCalledWith(connectedApp.user_group_name, {
        view: {all: true, identifiers: []},
    });
    expect(mockedProviders[1].save).toHaveBeenCalledWith(connectedApp.user_group_name, {
        view: {all: false, identifiers: ['code2A', 'code2B']},
    });
});

test('The connected app container notifies errors when saving permissions', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn().mockRejectedValue('some error occured'),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => {
        const [permissions, setPermissions] = useState({});

        return [mockedProviders, permissions, setPermissions];
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <ConnectedAppContainer connectedApp={connectedApp} />
        </NotifyContext.Provider>
    );

    await waitFor(() => {
        expect(ConnectedAppSettings).toHaveBeenCalled();
    });

    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();

    navigateToPermissionsAndFillTheFormAndSave();

    await waitFor(() => {
        expect(notify).toHaveBeenCalledTimes(2);
    });

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.connected_apps.edit.flash.save_permissions_error.description',
        {
            titleMessage:
                'akeneo_connectivity.connection.connect.connected_apps.edit.flash.save_permissions_error.title?entity=Provider1',
        }
    );
    expect(notify).toHaveBeenNthCalledWith(
        2,
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.connected_apps.edit.flash.success'
    );
    expect(screen.queryByText('pim_common.entity_updated')).toBeInTheDocument();
});

test('The connected app container saves monitoring settings', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_SOURCE, auditable: false},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <ConnectedAppContainer connectedApp={connectedApp} />
        </NotifyContext.Provider>
    );

    await waitFor(() => {
        expect(ConnectedAppSettings).toHaveBeenCalled();
    });

    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();

    fillTheMonitoringSettingsFormAndSave();

    await waitFor(() => {
        expect(notify).toHaveBeenCalledTimes(1);
    });

    expect(useSaveConnectedAppMonitoringSettings).toHaveBeenCalledWith(connectedApp.connection_code);
    expect(saveConnectedAppMonitoringSettings).toHaveBeenCalledWith({
        flowType: FlowType.DATA_DESTINATION,
        auditable: true,
    });
    expect(notify).toHaveBeenCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.connected_apps.edit.flash.success'
    );
    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();
});

test('The connected app container notifies errors when saving monitoring settings', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_SOURCE, auditable: false},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);
    saveConnectedAppMonitoringSettings.mockImplementation((data: MonitoringSettings) => Promise.reject());

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <ConnectedAppContainer connectedApp={connectedApp} />
        </NotifyContext.Provider>
    );

    await waitFor(() => {
        expect(ConnectedAppSettings).toHaveBeenCalled();
    });

    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();

    fillTheMonitoringSettingsFormAndSave();

    await waitFor(() => {
        expect(notify).toHaveBeenCalledTimes(1);
    });

    expect(useSaveConnectedAppMonitoringSettings).toHaveBeenCalledWith(connectedApp.connection_code);
    expect(saveConnectedAppMonitoringSettings).toHaveBeenCalledWith({
        flowType: FlowType.DATA_DESTINATION,
        auditable: true,
    });
    expect(notify).toHaveBeenCalledWith(
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.connected_apps.edit.flash.monitoring_settings_error.description'
    );
    expect(screen.queryByText('pim_common.entity_updated')).toBeInTheDocument();
});

test('Displaying a pending app comes with a warning message on the settings tab', async () => {
    const fetchConnectedAppMonitoringSettings: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=some_connection_code':
            {
                json: {flowType: FlowType.DATA_DESTINATION, auditable: true},
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppMonitoringSettings,
    });

    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];
    const mockedPermissions = {
        providerKey1: {
            view: {
                all: true,
                identifiers: [],
            },
        },
    };

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [
        mockedProviders,
        mockedPermissions,
        jest.fn(),
    ]);

    renderWithProviders(<ConnectedAppContainer connectedApp={{...connectedApp, is_pending: true}} />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings'));

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.pending')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.error_monitoring')
    ).toBeInTheDocument();
    act(() => {
        userEvent.click(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.error_monitoring')
        );
    });
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.pending')
    ).not.toBeInTheDocument();
    act(() => {
        userEvent.click(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
        );
    });
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.pending')
    ).not.toBeInTheDocument();
});

const assertPageHeader = () => {
    expect(screen.queryByText('pim_menu.tab.connect')).toBeInTheDocument();
    expect(screen.queryByText('pim_menu.item.connected_apps')).toBeInTheDocument();
    expect(screen.queryAllByText('App A')).toHaveLength(2);
    expect(screen.queryByText('pim_common.save')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    ).toBeInTheDocument();
};

const navigateToPermissionsAndFillTheFormAndSave = () => {
    // switch to "permissions" tab
    act(() => {
        userEvent.click(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
        );
    });

    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();

    // set some permissions (fill the form)
    act(() => {
        userEvent.click(screen.getByTestId('set-permissions'));
    });

    expect(screen.queryByText('pim_common.entity_updated')).toBeInTheDocument();

    // click on save button
    act(() => {
        userEvent.click(screen.getByText('pim_common.save'));
    });
};

const fillTheMonitoringSettingsFormAndSave = () => {
    // fill the form
    act(() => {
        userEvent.click(screen.getByTestId('set-monitoring-settings'));
    });

    expect(screen.queryByText('pim_common.entity_updated')).toBeInTheDocument();

    // click on save button
    act(() => {
        userEvent.click(screen.getByText('pim_common.save'));
    });
};
