import {useCatalogQuery} from '../../../hooks/useCatalogQuery';
import {AnyCriterionState} from '../../ProductSelection';
import {FilterValuesValues} from '../../FilterValues';

type Data = {
    id: string;
    name: string;
    enabled: boolean;
    product_selection_criteria: AnyCriterionState[];
    filter_values_criteria: FilterValuesValues;
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
