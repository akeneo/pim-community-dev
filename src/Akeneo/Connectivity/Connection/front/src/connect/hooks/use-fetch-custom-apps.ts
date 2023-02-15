import {useRoute} from '../../shared/router';
import {useCallback} from 'react';
import {CustomApps} from '../../model/app';

export const useFetchCustomApps = (): (() => Promise<CustomApps>) => {
    const url = useRoute('akeneo_connectivity_connection_custom_apps_rest_get_all');

    const fetchCustomApps = useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });
        if (!response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);

    return fetchCustomApps;
};
