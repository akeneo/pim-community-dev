import {useCallback} from 'react';
import {useRoute} from '../../shared/router';

export const useCreateTestApp = () => {
    const url = useRoute('akeneo_connectivity_connection_test_apps_rest_create');

    return useCallback(
        async (data: {name: string; activate_url: string; callback_url: string}) => {
            const response = await fetch(url, {
                method: 'POST',
                headers: [['X-Requested-With', 'XMLHttpRequest']],
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                return Promise.reject(`${response.status} ${response.statusText}`);
            }

            return response.json();
        },
        [url]
    );
};
