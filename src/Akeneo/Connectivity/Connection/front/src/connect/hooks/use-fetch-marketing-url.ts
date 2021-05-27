import {useCallback} from 'react';
import {useRoute} from '../../shared/router';

export const useFetchMarketingUrl = () => {
    const url = useRoute('akeneo_connectivity_connection_marketplace_url_rest_get');

    return useCallback(async () => {
        const response = await fetch(
            url,
            {
                method: 'GET',
                headers: [
                    ['X-Requested-With', 'XMLHttpRequest'],
                ]
            }
        );

        return response.json();
    }, [url]);
};
