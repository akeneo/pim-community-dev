import {useCallback} from 'react';
import {useRoute} from '../../shared/router';

export type TestApp = {
    name: string;
    activateUrl: string;
    callbackUrl: string;
};

export const useCreateTestApp = () => {
    const url = useRoute('akeneo_connectivity_connection_custom_apps_rest_create');

    return useCallback(
        async (testApp: TestApp) => {
            const response = await fetch(url, {
                method: 'POST',
                headers: [
                    ['Content-type', 'application/json'],
                    ['X-Requested-With', 'XMLHttpRequest'],
                ],
                body: JSON.stringify(testApp),
            });

            return response;
        },
        [url]
    );
};
