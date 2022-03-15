import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';
import {useFetchTestApps} from '@src/connect/hooks/use-fetch-test-apps';
import {useFeatureFlags} from '@src/shared/feature-flags';
import fetchMock from 'jest-fetch-mock';

jest.mock('@src/shared/feature-flags/use-feature-flags');

beforeEach(() => {
    jest.clearAllMocks();
    fetchMock.resetMocks();
});

test('it fetches the test apps', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                app_developer_mode: true,
                marketplace_activate: true,
            }[feature] ?? false),
    }));

    const expected = {
        total: 2,
        apps: [
            {
                id: '6fec7055-36ad-4301-9889-46c46ddd446a',
                name: 'Extension 1',
                author: 'Partner 1',
                activate_url: 'https://extension-1.test/activate',
                callback_url: 'https://extension-1.test/oauth2',
                connected: false,
            },
            {
                id: '896ae911-e877-46a0-b7c3-d7c572fe39ed',
                name: 'Extension 2',
                author: 'Partner 2',
                activate_url: 'https://extension-2.test/activate',
                callback_url: 'https://extension-2.test/oauth2',
                connected: false,
            },
        ],
    };
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_test_apps: {
            json: expected,
        },
    });
    const {result} = renderHook(() => useFetchTestApps());
    const response = await result.current();

    expect(response).toStrictEqual(expected);
});

test('it returns an empty response if the developer mode is disabled', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                app_developer_mode: false,
                marketplace_activate: true,
            }[feature] ?? false),
    }));

    const {result} = renderHook(() => useFetchTestApps());
    const response = await result.current();

    expect(response).toStrictEqual({total: 0, apps: []});
});
