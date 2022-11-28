import {useQuery} from 'react-query';
import {ProductMappingSchema} from '../components/ProductMapping/models/ProductMappingSchema';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: ProductMappingSchema | undefined;
    error: Error;
};

export const useProductMappingSchema = (catalogId: string): Result => {
    return useQuery<ProductMappingSchema, Error, ProductMappingSchema>(
        ['productMappingSchema', catalogId],
        async () => {
            const response = await fetch(`/rest/catalogs/${catalogId}/mapping-schemas/product`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            return await response.json();
        }
    );
};
