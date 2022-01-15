import {useRoute} from '../../shared/router';
import {useCallback} from 'react';
import {TestApps} from '../../model/app';
import {useFeatureFlags} from '../../shared/feature-flags';

export const useFetchTestApps = (): (() => Promise<TestApps>) => {
    const featureFlag = useFeatureFlags();
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

    if (!featureFlag.isEnabled('app_developer_mode')) {
        return () =>
            Promise.resolve({
                total: 0,
                apps: [],
            });
    }

    return fetchTestApps;
};
