import {useRoute} from '../../shared/router';
import {useCallback} from 'react';
import {useMutation} from 'react-query';

type DeleteCustomApp = () => Promise<void>;
export const useDeleteCustomApp = (customAppId: string): DeleteCustomApp => {
    const url = useRoute('akeneo_connectivity_connection_custom_apps_rest_delete', {customAppId});
    const request = useCallback(async () => {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
        });

        if (!response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return Promise.resolve();
    }, [url]);

    return useMutation(request).mutateAsync;
};
