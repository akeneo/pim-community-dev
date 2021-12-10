import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

type Result = {
    appName: string;
    appLogo: string;
    scopeMessages: Array<{
        icon: string;
        type: string;
        entities: string;
    }>;
    authenticationScopes: Array<'openid' | 'email' | 'profile'>;
};

export const useFetchAppWizardData = (clientId: string): (() => Promise<Result>) => {
    const url = useRoute('akeneo_connectivity_connection_apps_rest_get_wizard_data', {clientId: clientId});

    return useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });
        if (!response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);
};
