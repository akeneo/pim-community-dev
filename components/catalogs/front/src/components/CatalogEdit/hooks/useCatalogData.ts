import {CriteriaState} from '../../ProductSelection/models/Criteria';
import {useQuery} from 'react-query';

type CatalogData = {
    product_selection_criteria: CriteriaState;
};
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    error: ResultError;
    data: CatalogData | undefined;
};

export const useCatalogData = (id: string): Result => {
    return useQuery<CatalogData, ResultError>(['catalog_data', id], async () => {
        const response = await fetch(`/rest/catalogs/${id}/data`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
