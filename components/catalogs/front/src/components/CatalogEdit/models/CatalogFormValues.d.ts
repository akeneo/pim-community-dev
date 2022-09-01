import {ProductSelectionValues} from '../../ProductSelection';
import {FilterValuesValues} from '../../FilterValues';

export type CatalogFormValues = {
    enabled: boolean;
    product_selection_criteria: ProductSelectionValues;
    filter_values_criteria: FilterValuesValues;
};
