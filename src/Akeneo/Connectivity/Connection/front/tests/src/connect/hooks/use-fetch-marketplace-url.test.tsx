import {renderHook} from '@testing-library/react-hooks';
import {useMarketplaceUrl} from '@src/connect/hooks/use-fetch-marketing-url';
import {mockFetchResponses} from '../../../test-utils';

test('it fetches the marketplace url', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_url_rest_get: {
            json: 'http://marketplace.test',
        },
    });
    const {result} = renderHook(() => useMarketplaceUrl());
    const url = await result.current();

    expect(url).toBe('http://marketplace.test');
});
