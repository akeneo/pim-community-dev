import {useRoute} from '../../shared/router';
import {useCallback} from 'react';
import {TestApps} from '../../model/app';
import {useDeveloperMode} from './use-developer-mode';

export const useFetchTestApps = (): (() => Promise<TestApps>) => {
    const isDeveloperModeEnabled = useDeveloperMode();
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

    if (!isDeveloperModeEnabled) {
        return () =>
            Promise.resolve({
                total: 0,
                apps: [],
            });
    }

    return fetchTestApps;
};
