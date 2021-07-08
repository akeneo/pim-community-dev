import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';
import {useFetchExtensions} from '@src/connect/hooks/use-fetch-extensions';

test('it fetches the extensions', async () => {
    const expectedExtensions = {
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
            },
        ],
    };
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_extensions: {
            json: expectedExtensions,
        },
    });
    const {result} = renderHook(() => useFetchExtensions());
    const extensions = await result.current();

    expect(extensions).toStrictEqual(expectedExtensions);
});

test('it throws an error if the marketplace is unreachable', () => {
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_rest_get_all_extensions: {
            status: 400,
            json: '',
        },
    });

    expect(() => useFetchExtensions()).toThrow(Error);
});
