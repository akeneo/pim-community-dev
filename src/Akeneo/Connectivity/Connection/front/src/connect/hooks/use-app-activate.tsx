import {useCallback} from 'react';
import {useRoute} from '../../shared/router';

export const useAppActivate = (identifier: string) => {
    const url = useRoute('akeneo_connectivity_connection_app_activate', {identifier});

    return useCallback(async () => {
        await fetch(url);
    }, [url]);
};
