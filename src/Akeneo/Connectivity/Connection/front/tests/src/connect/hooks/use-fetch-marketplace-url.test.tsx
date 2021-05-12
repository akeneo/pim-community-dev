import {renderHook} from '@testing-library/react-hooks';
import {useFetchMarketingUrl} from '@src/connect/hooks/use-fetch-marketing-url';
import {mockFetchResponses} from '../../../test-utils';

test('it fetches the marketplace url', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_marketplace_url_rest_get: {
            json: 'http://marketplace.test',
        },
    });
    const {result} = renderHook(() => useFetchMarketingUrl());
    const url = await result.current();

    expect(url).toBe('http://marketplace.test');
});
