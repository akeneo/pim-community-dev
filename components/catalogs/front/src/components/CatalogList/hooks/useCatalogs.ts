import {useQuery} from 'react-query';

type Data = {
    id: string;
    name: string;
    enabled: boolean;
    owner_username: string;
}[];
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: ResultError;
};

export const useCatalogs = (owner: string): Result => {
    return useQuery<Data, ResultError, Data>(['catalogs_list', owner], async () => {
        const response = await fetch('/rest/catalogs?owner=' + owner, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
