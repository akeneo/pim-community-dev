import {useCallback} from 'react';
import {useRoute} from '../../shared/router';

export const useFetchMarketplaceUrl = () => {
    const url = useRoute('akeneo_connectivity_connection_marketplace_rest_get_web_marketplace_url');

    return useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });

        return response.json();
    }, [url]);
};
