import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';
import {useFetchApps} from '@src/connect/hooks/use-fetch-apps';

test('it fetches the apps', async () => {
    const expectedApps = {
        total: 2,
        extensions: [
            {
                id: '6fec7055-36ad-4301-9889-46c46ddd446a',
                name: 'Extension 1',
                logo: 'https://marketplace.test/logo/extension_1.png',
                author: 'Partner 1',
                partner: 'Akeneo Partner',
                description: 'Our Akeneo Connector',
                url: 'https://marketplace.test/extension/extension_1',
                categories: ['E-commerce'],
                certified: false,
                activate_url: 'https://extension-1.test/activate',
                callback_url: 'https://extension-1.test/oauth2',
            },
            {
                id: '896ae911-e877-46a0-b7c3-d7c572fe39ed',
                name: 'Extension 2',
                logo: 'https://marketplace.test/logo/extension_2.png',
                author: 'Partner 2',
                partner: 'Akeneo Preferred Partner',
                description: 'Our Akeneo Connector',
                url: 'https://marketplace.test/extension/extension_2',
                categories: ['E-commerce', 'Print'],
                certified: true,
                activate_url: 'https://extension-2.test/activate',
                callback_url: 'https://extension-2.test/oauth2',
            },
        ],
    };
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            json: expectedApps,
        },
    });
    const {result} = renderHook(() => useFetchApps());
    const apps = await result.current();

    expect(apps).toStrictEqual(expectedApps);
});

test('it throws an error if the marketplace is unreachable', () => {
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_apps: {
            status: 400,
            json: '',
        },
    });

    expect(() => useFetchApps()).toThrow(Error);
});
