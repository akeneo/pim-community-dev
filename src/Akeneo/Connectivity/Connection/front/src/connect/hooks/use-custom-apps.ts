import {useState, useEffect} from 'react';
import {CustomApps} from '../../model/app';
import {useFetchCustomApps} from './use-fetch-custom-apps';

interface Result {
    isLoading: boolean;
    customApps: CustomApps;
}

const defaultCustomAppsState = {total: 0, apps: []};

export const useCustomApps = (): Result => {
    const fetchCustomApps = useFetchCustomApps();

    const [customApps, setCustomApps] = useState<CustomApps>(defaultCustomAppsState);
    const [isLoading, setLoading] = useState<boolean>(true);

    useEffect(() => {
        fetchCustomApps()
            .then(setCustomApps)
            .catch(() => setCustomApps(defaultCustomAppsState))
            .finally(() => setLoading(false));
    }, []);

    return {isLoading, customApps};
};
