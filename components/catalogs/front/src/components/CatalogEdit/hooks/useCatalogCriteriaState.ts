import {useQuery} from 'react-query';
import {AnyCriterionState} from '../../ProductSelection';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    error: ResultError;
    data: AnyCriterionState[] | undefined;
};

export const useCatalogCriteriaState = (id: string): Result => {
    return useQuery<AnyCriterionState[], ResultError>(['catalog_criteria', id], async () => {
        const response = await fetch(`/rest/catalogs/${id}/data`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
