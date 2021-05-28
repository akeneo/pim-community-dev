import {renderHook} from '@testing-library/react-hooks';
import {useFetchMarketplaceUrl} from '@src/connect/hooks/use-fetch-marketplace-url';
import {mockFetchResponses} from '../../../test-utils';

test('it fetches the marketplace url', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_url_rest_get: {
            json: 'http://marketplace.test',
        },
    });
    const {result} = renderHook(() => useFetchMarketplaceUrl());
    const url = await result.current();

    expect(url).toBe('http://marketplace.test');
});
