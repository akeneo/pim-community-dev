import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

export const useDeleteApp = (connectionCode: string) => {
    const url = useRoute('akeneo_connectivity_connection_apps_rest_delete', {
        connectionCode: connectionCode,
    });

    return useCallback(async () => {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });

        if (!response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return Promise.resolve();
    }, [url]);
};
