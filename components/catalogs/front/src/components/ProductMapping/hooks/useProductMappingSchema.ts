import {useQuery} from 'react-query';
import {ProductMappingSchema} from '../models/ProductMappingSchema';

type Data = ProductMappingSchema;
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useProductMappingSchema = (catalogId: string): Result => {
    return useQuery<Data, Error, Data>(['productMappingSchema', catalogId], async () => {
        const response = await fetch(`/rest/catalogs/${catalogId}/mapping-schemas/product`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
