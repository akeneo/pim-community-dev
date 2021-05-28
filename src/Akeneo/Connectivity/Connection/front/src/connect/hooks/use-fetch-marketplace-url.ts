import {useCallback} from 'react';
import {useRoute} from '../../shared/router';

export const useFetchMarketplaceUrl = () => {
    const url = useRoute('akeneo_connectivity_connection_marketplace_url_rest_get');

    return useCallback(async () => {
        const response = await fetch(url);

        return response.json();
    }, [url]);
};
