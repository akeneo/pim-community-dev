import {useRoute} from '../router';
import {useState, useEffect, useCallback} from 'react';

interface MaxLimitReached {
    limitReached: boolean;
}

export const useConnectionsLimitReached = (): boolean => {
    const url = useRoute('akeneo_connectivity_connection_rest_connections_max_limit_reached');

    const [isLimitReached, setLimitReached] = useState<boolean>(false);

    const fetchCallback = useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });

        if (!response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return response.json().then((content: MaxLimitReached) => content.limitReached);
    }, [url]);

    useEffect(() => {
        fetchCallback()
            .then(setLimitReached)
            .catch(() => {
                setLimitReached(true);
            });
    }, []);

    return isLimitReached;
};
