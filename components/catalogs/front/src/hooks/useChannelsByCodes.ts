import {useQuery} from 'react-query';
import {Channel} from '../models/Channel';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Channel[] | undefined;
    error: ResultError;
};

export const useChannelsByCodes = (codes: string[] | undefined): Result => {
    return useQuery<Channel[], ResultError, Channel[]>(['channels', codes?.sort().join('')], async () => {
        if (undefined === codes || codes.length === 0) {
            return [];
        }
        const _codes = codes.join(',');

        const response = await fetch(`/rest/catalogs/channels?codes=${_codes}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
