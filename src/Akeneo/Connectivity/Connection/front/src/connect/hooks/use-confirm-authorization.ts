import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

interface ConfirmReturn {
    appId: string;
    userGroup: string;
    redirectUrl: string;
}

interface Error {
    message: string;
    property_path: string;
}

export interface RejectReason {
    status: number;
    statusText: string;
    errors: Error[];
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
            const json = await response.json();

            return Promise.reject({
                status: response.status,
                statusText: response.statusText,
                errors: json?.errors ?? [],
            });
        }

        return response.json();
    }, [url]);
};
