import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

export interface ScopeMessage {
    icon: string,
    type: string,
    entities: string,
}

export interface AppWizardData {
    appName: string,
    appLogo: string,
    scopeMessages:  ScopeMessage[]
}

export const useFetchAppWizardData = (clientId: string) => {
    const url = useRoute('akeneo_connectivity_connection_apps_rest_get_wizard_data', {clientId: clientId});

    return useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });
        if (!response.ok) {
            throw new Error(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);
};
