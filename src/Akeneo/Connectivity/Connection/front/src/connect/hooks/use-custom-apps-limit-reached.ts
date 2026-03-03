import {useRoute} from '../../shared/router';
import {useQuery} from 'react-query';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: boolean | undefined;
    error: Error;
};

export const useCustomAppsLimitReached = (): Result => {
    const url = useRoute('akeneo_connectivity_connection_custom_apps_rest_max_limit_reached');

    return useQuery<boolean, Error, boolean>(['custom-apps-limit-reached'], async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        return await response.json();
    });
};
