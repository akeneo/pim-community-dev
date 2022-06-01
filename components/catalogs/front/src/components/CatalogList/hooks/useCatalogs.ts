import {useQuery} from 'react-query';

type Data = {
    id: string;
    name: string;
    enabled: boolean;
}[];
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useCatalogs = (owner: string): Result => {
    return useQuery<Data, Error, Data>(['catalogs_list', owner], async () => {
        const response = await fetch('/rest/catalogs?owner=' + owner, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
