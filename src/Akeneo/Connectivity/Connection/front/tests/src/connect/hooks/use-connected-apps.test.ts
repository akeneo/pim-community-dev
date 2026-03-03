import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';
import {useConnectedApps} from '@src/connect/hooks/use-connected-apps';
import {useFeatureFlags} from '@src/shared/feature-flags/use-feature-flags';
import {NotificationLevel, useNotify} from '@src/shared/notify';
import {CustomApp} from '@src/model/app';
import {ConnectedApp} from '@src/model/Apps/connected-app';
import {useTriggerConnectedAppRefresh} from '@src/connect/hooks/use-trigger-connected-app-refresh';

jest.mock('@src/shared/feature-flags/use-feature-flags');
jest.mock('@src/shared/notify');
jest.mock('@src/connect/hooks/use-trigger-connected-app-refresh');

const notify = jest.fn();

beforeEach(() => {
    jest.clearAllMocks();
});

test('it returns an empty list if the feature flag is disabled', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) => {
            switch (feature) {
                case 'marketplace_activate':
                    return false;
            }
        },
    }));

    const {result} = renderHook(() => useConnectedApps());
    expect(result.current).toEqual([]);
});

test('it notifies if it cannot retrieve connected apps', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) => {
            switch (feature) {
                case 'marketplace_activate':
                    return true;
            }
        },
    }));
    (useNotify as jest.Mock).mockImplementation(() => notify);

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApps());
    await waitForNextUpdate();
    expect(result.current).toEqual(false);
    expect(notify).toBeCalledWith(
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.connected_apps.list.flash.error'
    );
});

test('it does not fetch the marketplace apps if there is no connected apps', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) => {
            switch (feature) {
                case 'marketplace_activate':
                    return true;
            }
        },
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_apps_rest_get_all_connected_apps: {
            json: [],
        },
    });

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApps());
    expect(result.current).toEqual(null);
    await waitForNextUpdate();
    expect(result.current).toEqual([]);
});

test('it does not fail if it cannot retrieve marketplace apps', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) => {
            switch (feature) {
                case 'marketplace_activate':
                    return true;
            }
        },
    }));

    const connectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.com/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        is_pending: false,
    };

    mockFetchResponses({
        akeneo_connectivity_connection_apps_rest_get_all_connected_apps: {
            json: [connectedApp],
        },
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            reject: true,
            json: {},
        },
    });

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApps());
    expect(result.current).toEqual(null);
    await waitForNextUpdate();
    expect(result.current).toEqual([connectedApp]);
});

test('it fetches connected apps', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) => {
            switch (feature) {
                case 'marketplace_activate':
                    return true;
            }
        },
    }));

    const connectedApp: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.com/path/to/logo/a',
        activate_url: 'https://example.com/activate',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        is_custom_app: false,
        is_listed_on_the_appstore: true,
        is_loaded: true,
        is_pending: false,
        has_outdated_scopes: false,
    };

    const marketplaceApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'Extension 1',
        logo: 'http://www.example.com/path/to/logo/a',
        author: 'Partner 1',
        partner: 'Akeneo Partner',
        description: 'Our Akeneo Connector',
        url: 'https://marketplace.akeneo.com/extension/extension_1',
        categories: ['E-commerce'],
        certified: false,
        activate_url: 'https://example.com/activate',
        callback_url: 'https://example.com/oauth2',
    };

    const expectedApp = {
        ...connectedApp,
        activate_url: marketplaceApp.activate_url,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    mockFetchResponses({
        akeneo_connectivity_connection_apps_rest_get_all_connected_apps: {
            json: [connectedApp],
        },
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            json: {
                total: 1,
                apps: [marketplaceApp],
            },
        },
    });

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApps());
    expect(result.current).toEqual(null);
    await waitForNextUpdate();
    expect(result.current).toEqual([expectedApp]);
});

test('it fetches connected custom apps', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) => {
            switch (feature) {
                case 'marketplace_activate':
                    return true;
            }
        },
    }));

    const connectedApp: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.com/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        is_custom_app: true,
        is_pending: false,
        has_outdated_scopes: false,
    };

    const customApp: CustomApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'Extension 1',
        logo: null,
        author: 'Partner 1',
        url: null,
        activate_url: 'https://example.com/activate',
        callback_url: 'https://example.com/oauth2',
        connected: true,
    };

    const expectedApp = {
        ...connectedApp,
        activate_url: customApp.activate_url,
        is_loaded: true,
        is_listed_on_the_appstore: false,
    };

    mockFetchResponses({
        akeneo_connectivity_connection_apps_rest_get_all_connected_apps: {
            json: [connectedApp],
        },
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            json: {
                total: 0,
                apps: [],
            },
        },
        akeneo_connectivity_connection_custom_apps_rest_get_all: {
            json: {
                total: 1,
                apps: [customApp],
            },
        },
    });

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApps());
    expect(result.current).toEqual(null);
    await waitForNextUpdate();
    expect(result.current).toEqual([expectedApp]);
});

test('it returns connected apps and warns when not listed on the appstore', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) => {
            switch (feature) {
                case 'marketplace_activate':
                    return true;
            }
        },
    }));

    const connectedApp: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.com/path/to/logo/a',
        activate_url: undefined,
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        is_custom_app: false,
        is_listed_on_the_appstore: false,
        is_loaded: true,
        is_pending: false,
        has_outdated_scopes: false,
    };

    const expectedApp = {
        ...connectedApp,
        activate_url: undefined,
        is_loaded: true,
        is_listed_on_the_appstore: false,
    };

    mockFetchResponses({
        akeneo_connectivity_connection_apps_rest_get_all_connected_apps: {
            json: [connectedApp],
        },
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            json: {
                total: 0,
                apps: [],
            },
        },
    });

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApps());
    expect(result.current).toEqual(null);
    await waitForNextUpdate();
    expect(result.current).toEqual([expectedApp]);
});

test('it triggers a connected app update if there is inconsistency between it and the app store data', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) => {
            switch (feature) {
                case 'marketplace_activate':
                    return true;
            }
        },
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_apps_rest_get_all_connected_apps: {
            json: [
                {
                    id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                    name: 'App A',
                    scopes: ['scope A1'],
                    connection_code: 'connectionCodeA',
                    logo: 'http://www.example.com/path/to/logo/a',
                    author: 'author A',
                    user_group_name: 'app_123456abcde',
                    categories: ['E-commerce'],
                    certified: false,
                    partner: 'Akeneo Partner',
                },
                {
                    id: '0dfce574-2238-4b13-b8cc-8d257ce7645c',
                    name: 'App B',
                    scopes: ['scope A1'],
                    connection_code: 'connectionCodeB',
                    logo: 'http://www.example.com/path/to/logo/a',
                    author: 'author A',
                    user_group_name: 'app_123456abcde',
                    categories: ['E-commerce'],
                    certified: false,
                    partner: 'Akeneo Partner',
                },
            ],
        },
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            json: {
                total: 2,
                apps: [
                    {
                        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                        name: 'App A WITH NEW TITLE',
                        logo: 'http://www.example.com/path/to/logo/a',
                        author: 'author A',
                        partner: 'Akeneo Partner',
                        description: 'Our Akeneo Connector',
                        url: 'https://marketplace.akeneo.com/extension/extension_1',
                        categories: ['E-commerce'],
                        certified: false,
                        activate_url: 'https://example.com/activate',
                        callback_url: 'https://example.com/oauth2',
                    },
                    {
                        id: '0dfce574-2238-4b13-b8cc-8d257ce7645c',
                        name: 'App B',
                        logo: 'http://www.example.com/path/to/logo/a',
                        author: 'author A',
                        partner: 'Akeneo Partner',
                        description: 'Our Akeneo Connector',
                        url: 'https://marketplace.akeneo.com/extension/extension_1',
                        categories: ['E-commerce'],
                        certified: false,
                        activate_url: 'https://example.com/activate',
                        callback_url: 'https://example.com/oauth2',
                    },
                ],
            },
        },
        akeneo_connectivity_connection_custom_apps_rest_get_all: {
            json: {
                total: 1,
                apps: [
                    {
                        id: 'b85f3b1a-a887-11ed-afa1-0242ac120002',
                        name: 'Custom App C',
                        scopes: ['scope C1'],
                        connection_code: 'connectionCodeC',
                        logo: 'http://www.example.com/path/to/logo/c',
                        author: 'author C',
                        user_group_name: 'app_custom_654321',
                        connection_username: 'Connection Username',
                        categories: ['category C1', 'category C2'],
                        certified: false,
                        partner: 'partner C',
                        is_custom_app: true,
                        is_pending: false,
                        has_outdated_scopes: false,
                    },
                ],
            },
        },
    });

    const triggerConnectedAppRefresh = jest.fn();
    (useTriggerConnectedAppRefresh as jest.Mock).mockImplementation(() => triggerConnectedAppRefresh);

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApps());
    expect(result.current).toEqual(null);
    await waitForNextUpdate();
    expect(triggerConnectedAppRefresh).toHaveBeenCalledWith('connectionCodeA');
    expect(triggerConnectedAppRefresh).not.toHaveBeenCalledWith('connectionCodeB');
    expect(triggerConnectedAppRefresh).not.toHaveBeenCalledWith('connectionCodeC');
});
