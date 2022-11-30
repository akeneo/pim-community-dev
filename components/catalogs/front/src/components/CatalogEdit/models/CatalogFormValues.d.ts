import {ProductSelectionValues} from '../../ProductSelection';
import {ProductValueFiltersValues} from '../../ProductValueFilters';
import {ProductMapping} from '../../ProductMapping/models/ProductMapping';

export type CatalogFormValues = {
    enabled: boolean;
    product_selection_criteria: ProductSelectionValues;
    product_value_filters: ProductValueFiltersValues;
    product_mapping: ProductMapping;
};
