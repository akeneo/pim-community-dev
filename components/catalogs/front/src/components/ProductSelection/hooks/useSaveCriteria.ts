import {useMutation} from 'react-query';
import {CriterionStates} from '../models/Criteria';
import {UseMutateFunction} from 'react-query/types/react/types';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: undefined | void;
    error: Error;
    mutate: UseMutateFunction<undefined | void, Error, CriterionStates[]>;
};

export const useSaveCriteria = (catalogId: string): Result => {
    return useMutation<undefined | void, Error, CriterionStates[]>(async (criteria: CriterionStates[]) => {
        await fetch('/rest/catalogs/' + catalogId + '/save-criteria', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(criteria),
        });
    });
};
