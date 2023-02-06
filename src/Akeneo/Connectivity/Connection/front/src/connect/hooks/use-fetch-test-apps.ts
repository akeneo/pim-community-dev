import {useRoute} from '../../shared/router';
import {useCallback} from 'react';
import {TestApps} from '../../model/app';

export const useFetchTestApps = (): (() => Promise<TestApps>) => {
    const url = useRoute('akeneo_connectivity_connection_marketplace_rest_get_all_test_apps');

    const fetchTestApps = useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });
        if (!response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);

    return fetchTestApps;
};
