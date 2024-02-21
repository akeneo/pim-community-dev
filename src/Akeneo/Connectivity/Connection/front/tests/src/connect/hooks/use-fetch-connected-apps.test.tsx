import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';
import {useFetchConnectedApps} from '@src/connect/hooks/use-fetch-connected-apps';

test('it fetches the connected apps', async () => {
    const expectedConnectedApps = [
        {
            id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            name: 'App A',
            scopes: ['scope A1'],
            connection_code: 'connectionCodeA',
            logo: 'http://www.example.test/path/to/logo/a',
            author: 'author A',
            user_group_name: 'app_123456abcde',
            categories: ['category A1', 'category A2'],
            certified: false,
            partner: 'partner A',
        },
        {
            id: '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            name: 'App B',
            scopes: ['scope B1', 'scope B2'],
            connection_code: 'connectionCodeB',
            logo: 'http://www.example.test/path/to/logo/b',
            author: 'author B',
            user_group_name: 'app_7891011ghijklm',
            categories: ['category B1'],
            certified: true,
            partner: null,
        },
    ];

    mockFetchResponses({
        akeneo_connectivity_connection_apps_rest_get_all_connected_apps: {
            json: expectedConnectedApps,
        },
    });
    const {result} = renderHook(() => useFetchConnectedApps());
    const connectedApps = await result.current();

    expect(connectedApps).toStrictEqual(expectedConnectedApps);
});
