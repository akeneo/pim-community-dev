import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

type Result = {
    appName: string;
    appLogo: string;
    appUrl: string | null;
    appIsCertified: boolean;
    scopeMessages: Array<{
        icon: string;
        type: string;
        entities: string;
    }>;
    oldScopeMessages: Array<{
        icon: string;
        type: string;
        entities: string;
    }> | null;
    authenticationScopes: Array<'email' | 'profile'>;
    oldAuthenticationScopes: Array<'email' | 'profile'> | null;
    displayCheckboxConsent: boolean;
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
