import {ProductSelectionValues} from '../../ProductSelection';
import {ProductValueFiltersValues} from '../../ProductValueFilters';

export type CatalogFormValues = {
    enabled: boolean;
    product_selection_criteria: ProductSelectionValues;
    product_value_filters: ProductValueFiltersValues;
};
