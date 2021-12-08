import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

interface ConfirmReturn {
    appId: string;
    userGroup: string;
    redirectUrl: string;
}

type hookType = (clientId: string) => () => Promise<ConfirmReturn>;

export const useConfirmAuthorization: hookType = clientId => {
    const url = useRoute('akeneo_connectivity_connection_apps_rest_confirm_authorization', {clientId: clientId});

    return useCallback(async () => {
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
