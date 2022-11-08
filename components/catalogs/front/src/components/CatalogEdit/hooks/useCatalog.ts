import {useCatalogQuery} from '../../../hooks/useCatalogQuery';
import {AnyCriterionState} from '../../ProductSelection';
import {ProductValueFiltersValues} from '../../ProductValueFilters';
import {ProductMapping} from '../../ProductMapping/models/ProductMapping';

type Data = {
    id: string;
    name: string;
    enabled: boolean;
    owner_username: string;
    product_selection_criteria: AnyCriterionState[];
    product_value_filters: ProductValueFiltersValues;
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
