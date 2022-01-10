import {useRoute} from '../../shared/router';
import {useState, useEffect, useCallback} from 'react';
import {TestApps} from '../../model/app';
import {useFeatureFlags} from '../../shared/feature-flags';

interface FetchTestApps {
    isLoading: boolean;
    testApps: TestApps;
}

const defaultTestAppsState = {total: 0, apps: []};

export const useFetchTestApps = (): FetchTestApps => {
    const featureFlag = useFeatureFlags();
    const url = useRoute('akeneo_connectivity_connection_marketplace_rest_get_all_test_apps');

    const [testApps, setTestApps] = useState<TestApps>(defaultTestAppsState);
    const [isLoading, setLoading] = useState<boolean>(true);

    const fetchCallback = useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });

        if (!response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);

    useEffect(() => {
        if (!featureFlag.isEnabled('app_developer_mode')) {
            setLoading(false);
            return;
        }

        fetchCallback()
            .then(setTestApps)
            .catch(() => setTestApps(defaultTestAppsState))
            .finally(() => setLoading(false));
    }, []);

    return {isLoading, testApps};
};
