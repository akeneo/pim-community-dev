import {useQuery} from 'react-query';

type Data = string[];
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: ResultError;
};

export const useChannelCurrencies = (channelCode: string | null): Result => {
    return useQuery<Data, ResultError, Data>(['channel_currencies', channelCode], async () => {
        if (null === channelCode) {
            return [];
        }

        const response = await fetch(`/rest/catalogs/channels/${channelCode}/currencies`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
