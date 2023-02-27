import {useQuery} from 'react-query';
import {CatalogFormErrors} from '../models/CatalogFormErrors';

type Data = CatalogFormErrors;
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useCatalogErrors = (catalogId: string): Result => {
    return useQuery<Data, Error, Data>(
        'catalogErrors',
        async () => {
            const response = await fetch(`/rest/catalogs/${catalogId}/errors`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            return await response.json();
        },
        {cacheTime: 0}
    );
};
