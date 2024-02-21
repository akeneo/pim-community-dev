import {useCallback} from 'react';
import {useRoute} from '../../shared/router';

export const useConfirmAuthentication = (clientId: string) => {
    const url = useRoute('akeneo_connectivity_connection_apps_rest_confirm_authentication', {
        clientId,
    });

    return useCallback(async (): Promise<{redirectUrl: string}> => {
        const response = await fetch(url, {
            method: 'POST',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });
        if (!response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);
};
