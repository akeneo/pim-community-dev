import {useQuery} from 'react-query';
import {Attributes} from '../models/Attributes';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Attributes | undefined;
    error: Error;
};

export const useProductMappingAttributes = (catalogId: string): Result => {
    return useQuery<Attributes, Error, Attributes>(['product-mapping-attributes', catalogId], async () => {
        const response = await fetch(`/rest/catalogs/${catalogId}/mapping/product/attributes`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
