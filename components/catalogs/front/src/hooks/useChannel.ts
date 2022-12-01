import {useQuery} from 'react-query';
import {Channel} from '../models/Channel';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Channel | null | undefined;
    error: ResultError;
};

export const useChannel = (code: string | null): Result => {
    return useQuery<Channel, ResultError, Channel>(['channel', code], async () => {
        if (null === code) {
            return null;
        }

        const response = await fetch('/rest/catalogs/channels/' + code, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
