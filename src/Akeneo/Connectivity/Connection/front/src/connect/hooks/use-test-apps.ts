import {useState, useEffect} from 'react';
import {TestApps} from '../../model/app';
import {useFetchTestApps} from './use-fetch-test-apps';

interface Result {
    isLoading: boolean;
    testApps: TestApps;
}

const defaultTestAppsState = {total: 0, apps: []};

export const useTestApps = (): Result => {
    const fetchTestApps = useFetchTestApps();

    const [testApps, setTestApps] = useState<TestApps>(defaultTestAppsState);
    const [isLoading, setLoading] = useState<boolean>(true);

    useEffect(() => {
        fetchTestApps()
            .then(setTestApps)
            .catch(() => setTestApps(defaultTestAppsState))
            .finally(() => setLoading(false));
    }, []);

    return {isLoading, testApps};
};
