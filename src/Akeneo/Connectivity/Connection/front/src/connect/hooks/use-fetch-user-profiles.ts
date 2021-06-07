import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

export const useFetchUserProfiles = () => {
    const url = useRoute('pim_user_rest_find_all_profiles');

    return useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });

        return response.json();
    }, [url]);
};
