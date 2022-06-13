import {useQuery} from 'react-query';

type Data = {
    id: string;
    name: string;
    enabled: boolean;
    owner_username: string;
};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useCatalog = (catalogId: string): Result => {
    return useQuery<Data, Error, Data>(['catalog_item', catalogId], async () => {
        const response = await fetch('/rest/catalogs/' + catalogId, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
