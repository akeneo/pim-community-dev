import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';
import {useFetchTestApps} from '@src/connect/hooks/use-fetch-test-apps';
import fetchMock from 'jest-fetch-mock';
import {useFeatureFlags} from '@src/shared/feature-flags';

const emptyTestApps = {total: 0, apps: []};
const testApps = {
    total: 2,
    apps: [
        {
            id: 'id1',
            name: 'testApp1',
            author: 'AuthorName',
            activate_url: 'test_app_1_activate_url',
            callback_url: 'test_app_1_callback_url',
            connected: false,
        },
        {
            id: 'id2',
            name: 'testApp2',
            author: null,
            activate_url: 'test_app_2_activate_url',
            callback_url: 'test_app_2_callback_url',
            connected: true,
        },
    ],
};

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    ...jest.requireActual('@src/shared/feature-flags/use-feature-flags'),
    useFeatureFlags: jest.fn(() => {
        return {
            isEnabled: () => true,
        };
    }),
}));

beforeEach(() => {
    jest.clearAllMocks();
    fetchMock.resetMocks();
});

test('it returns loading status and testApps values', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_test_apps: {
            json: testApps,
        },
    });

    const {result, waitForNextUpdate} = renderHook(() => useFetchTestApps());

    expect(result.current).toStrictEqual({
        isLoading: true,
        testApps: emptyTestApps,
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        isLoading: false,
        testApps: testApps,
    });
});

test('it returns loading status and empty values on fetch error ', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_test_apps: {
            reject: true,
            json: {},
        },
    });

    const {result, waitForNextUpdate} = renderHook(() => useFetchTestApps());

    expect(result.current).toStrictEqual({
        isLoading: true,
        testApps: emptyTestApps,
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        isLoading: false,
        testApps: emptyTestApps,
    });
});

test('it returns loading status and empty values with feature flag disabled', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({isEnabled: () => false}));

    const {result} = renderHook(() => useFetchTestApps());

    expect(result.current).toStrictEqual({
        isLoading: false,
        testApps: emptyTestApps,
    });
});
