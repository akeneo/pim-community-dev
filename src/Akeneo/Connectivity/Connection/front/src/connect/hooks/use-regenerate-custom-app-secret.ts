import {useCallback} from 'react';
import {useRouter} from '../../shared/router/use-router';
import {useMutation, useQueryClient} from 'react-query';

export const useRegenerateCustomAppSecret = () => {
    const generateUrl = useRouter();
    const queryClient = useQueryClient();

    const request = useCallback(async (id: string) => {
        const response = await fetch(
            generateUrl('akeneo_connectivity_connection_custom_apps_rest_regenerate_secret', {customAppId: id}),
            {
                method: 'POST',
                headers: [
                    ['Content-type', 'application/json'],
                    ['X-Requested-With', 'XMLHttpRequest'],
                ],
            }
        );

        if (!response.ok) {
            throw new Error(`${response.status} ${response.statusText}`);
        }

        return await response.json();
    }, []);

    return useMutation<string, string, string>(request, {
        onSuccess: () => queryClient.invalidateQueries('custom_app_secret'),
    });
};
