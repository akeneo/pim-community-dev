import {useCatalogQuery} from '../../../hooks/useCatalogQuery';
import {ProductMapping} from '../models/ProductMapping';

type Data = {
    id: string;
    product_mapping: ProductMapping;
    has_product_mapping_schema: boolean;
};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useCatalog = (catalogId: string): Result => {
    return useCatalogQuery(catalogId);
};
