import {useCallback} from 'react';
import {useRouter} from '../../shared/router/use-router';

type Callback = (id: string) => Promise<boolean>;

export const useTriggerConnectedAppRefresh = (): Callback => {
    const router = useRouter();

    return useCallback(
        async (connectionCode: string): Promise<boolean> => {
            const url = router('akeneo_connectivity_connection_apps_rest_refresh', {connectionCode: connectionCode});

            const response = await fetch(url, {
                method: 'POST',
                headers: [['X-Requested-With', 'XMLHttpRequest']],
            });

            return response.ok;
        },
        [router]
    );
};
