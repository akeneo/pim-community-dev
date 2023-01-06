import {useQuery} from 'react-query';
import {ProductMapping as ProductMappingType} from '../../ProductMapping/models/ProductMapping';

type PreviewMappedProduct = {
    uuid: string;
}

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: PreviewMappedProduct | null | undefined;
    error: ResultError;
};

export const usePreviewMappedProduct = (
    catalogId: string,
    productId: string,
    productMapping: ProductMappingType): Result => {
    return useQuery<PreviewMappedProduct, ResultError, PreviewMappedProduct>(['mappedProduct', productId], async () => {
        if ('' === productId) {
            return null;
        }

        const rawProductMapping = JSON.stringify(productMapping);

        const response = await fetch('/rest/catalogs/' + catalogId + '/preview-mapped-products/' + productId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: rawProductMapping,
        });

        return await response.json();
    });
};
