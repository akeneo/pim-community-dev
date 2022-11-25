import {useQuery} from 'react-query';
import {AnyCriterionState} from '../components/ProductSelection';
import {ProductValueFiltersValues} from '../components/ProductValueFilters';
import {ProductMapping} from '../components/ProductMapping/models/ProductMapping';

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

export const useCatalogQuery = (catalogId: string): Result => {
    return useQuery<Data, Error, Data>(['catalog', catalogId], async () => {
        const response = await fetch('/rest/catalogs/' + catalogId, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
