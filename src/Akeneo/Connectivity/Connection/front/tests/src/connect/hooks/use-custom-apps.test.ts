import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';
import {useCustomApps} from '@src/connect/hooks/use-custom-apps';
import fetchMock from 'jest-fetch-mock';
import {useFeatureFlags} from '@src/shared/feature-flags';

const emptyCustomApps = {total: 0, apps: []};
const customApps = {
    total: 2,
    apps: [
        {
            id: 'id1',
            name: 'customApp1',
            author: 'AuthorName',
            activate_url: 'custom_app_1_activate_url',
            callback_url: 'custom_app_1_callback_url',
            connected: false,
        },
        {
            id: 'id2',
            name: 'customApp2',
            author: null,
            activate_url: 'custom_app_2_activate_url',
            callback_url: 'custom_app_2_callback_url',
            connected: true,
        },
    ],
};

jest.mock('@src/shared/feature-flags/use-feature-flags');

beforeEach(() => {
    jest.clearAllMocks();
    fetchMock.resetMocks();
});

test('it returns loading status and customApps values', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
            }[feature] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_get_all: {
            json: customApps,
        },
    });

    const {result, waitForNextUpdate} = renderHook(() => useCustomApps());

    expect(result.current).toStrictEqual({
        isLoading: true,
        customApps: emptyCustomApps,
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        isLoading: false,
        customApps: customApps,
    });
});

test('it returns loading status and empty values on fetch error ', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
            }[feature] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_get_all: {
            reject: true,
            json: {},
        },
    });

    const {result, waitForNextUpdate} = renderHook(() => useCustomApps());

    expect(result.current).toStrictEqual({
        isLoading: true,
        customApps: emptyCustomApps,
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        isLoading: false,
        customApps: emptyCustomApps,
    });
});

test('it returns loading status and empty values with feature flag disabled', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
            }[feature] ?? false),
    }));

    const {result, waitForNextUpdate} = renderHook(() => useCustomApps());

    expect(result.current).toStrictEqual({
        isLoading: true,
        customApps: emptyCustomApps,
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        isLoading: false,
        customApps: emptyCustomApps,
    });
});
