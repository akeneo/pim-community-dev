import {useQuery} from 'react-query';

type Result = {
    isLoading: boolean;
    isError: boolean;
    data?: Data;
    error: string | null;
};
type Data = {
    id: string;
    name: string;
    enabled: boolean;
}[];

export const useCatalogs = (owner: string): Result => {
    return useQuery<Data, string|null, Data>(
        ['catalogs_list', owner],
        async (): Promise<Data> => {
            const response = await fetch(
                '/rest/catalogs?owner=' + owner,
                {
                    'headers': {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );
            return await response.json();
        }
    );
};
