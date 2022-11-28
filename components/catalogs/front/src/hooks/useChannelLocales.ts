import {useQuery} from 'react-query';
import {Locale} from '../components/ProductSelection/models/Locale';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Locale[] | undefined;
    error: ResultError;
};

export const useChannelLocales = (channelCode: string | null): Result => {
    return useQuery<Locale[], ResultError, Locale[]>(['channel_locales', channelCode], async () => {
        if (null === channelCode) {
            return [];
        }

        const response = await fetch('/rest/catalogs/channels/' + channelCode + '/locales', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
