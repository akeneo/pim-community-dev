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

export const useSaveCriteria = (catalogId: string, onSuccess: () => void, onError: () => void): Result => {
    return useMutation<undefined | void, Error, CriterionStates[]>(
        async (criteria: CriterionStates[]) => {
            const response = await fetch('/rest/catalogs/' + catalogId + '/save-criteria', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(criteria),
            });

            if (!response.ok) {
                throw new Error(response.statusText);
            }
        },
        {
            onError: (error: Error, variables: CriterionStates[]) => {
                onError();
            },
            onSuccess: (data: undefined | void, variables: CriterionStates[]) => {
                onSuccess();
            },
        }
    );
};
