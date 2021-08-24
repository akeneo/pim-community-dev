import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

export const useFetchApps = () => {
    const url = useRoute('akeneo_connectivity_connection_marketplace_rest_get_all_apps');

    return useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });
        if (false === response.ok) {
            throw new Error(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);
};
