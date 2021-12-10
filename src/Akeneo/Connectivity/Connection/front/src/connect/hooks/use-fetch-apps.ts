import {useRoute} from '../../shared/router';
import {useCallback} from 'react';
import {Apps} from '../../model/app';

export const useFetchApps = (): (() => Promise<Apps>) => {
    const url = useRoute('akeneo_connectivity_connection_marketplace_rest_get_all_apps');

    return useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });
        if (false === response.ok || 204 === response.status) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);
};
