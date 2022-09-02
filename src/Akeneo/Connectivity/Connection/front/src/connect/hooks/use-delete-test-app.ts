import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

type CallbackType = () => Promise<void>;

export const useDeleteTestApp = (testAppId: string): CallbackType => {
    const url = useRoute('akeneo_connectivity_connection_marketplace_rest_test_apps_delete', {testAppId});

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
