import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

type CallbackType = () => Promise<void>;

export const useDeleteTestApp = (customAppId: string): CallbackType => {
    const url = useRoute('akeneo_connectivity_connection_custom_apps_rest_delete', {customAppId});

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
