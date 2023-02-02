import {useQuery} from 'react-query';

type Data = string[];
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: ResultError;
};

export const useChannelCurrencies = (channelCode: string): Result => {
    return useQuery<Data, ResultError, Data>(['currencies', channelCode], async () => {
        const response = await fetch(`/rest/catalogs/channels/${channelCode}/currencies`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
