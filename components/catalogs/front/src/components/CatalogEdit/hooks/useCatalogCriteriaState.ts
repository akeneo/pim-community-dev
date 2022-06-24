import {CriteriaState} from '../../ProductSelection/models/Criteria';
import {useQuery} from 'react-query';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    error: ResultError;
    data: CriteriaState | undefined;
};

export const useCatalogCriteriaState = (id: string): Result => {
    return useQuery<CriteriaState, ResultError>(['catalog_criteria', id], async () => {
        const response = await fetch(`/rest/catalogs/${id}/data`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
