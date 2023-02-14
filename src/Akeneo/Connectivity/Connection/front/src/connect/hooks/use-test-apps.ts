import {useState, useEffect} from 'react';
import {CustomApps} from '../../model/app';
import {useFetchTestApps} from './use-fetch-test-apps';

interface Result {
    isLoading: boolean;
    testApps: CustomApps;
}

const defaultCustomAppsState = {total: 0, apps: []};

export const useTestApps = (): Result => {
    const fetchTestApps = useFetchTestApps();

    const [testApps, setTestApps] = useState<CustomApps>(defaultCustomAppsState);
    const [isLoading, setLoading] = useState<boolean>(true);

    useEffect(() => {
        fetchTestApps()
            .then(setTestApps)
            .catch(() => setTestApps(defaultCustomAppsState))
            .finally(() => setLoading(false));
    }, []);

    return {isLoading, testApps};
};
