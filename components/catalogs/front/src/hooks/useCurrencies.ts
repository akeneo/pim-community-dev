import {useQuery} from 'react-query';

type Data = string[];
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: ResultError;
};

export const useCurrencies = (): Result => {
    return useQuery<Data, ResultError, Data>('currencies', async () => {
        const response = await fetch('/rest/catalogs/currencies', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
