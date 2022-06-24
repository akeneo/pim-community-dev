import {useMutation} from 'react-query';
import {AnyCriterionState} from '../models/Criteria';
import {UseMutateFunction} from 'react-query/types/react/types';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: undefined | void;
    error: Error;
    mutate: UseMutateFunction<undefined | void, Error, AnyCriterionState[]>;
};

export const useSaveCriteria = (catalogId: string, onSuccess: () => void, onError: () => void): Result => {
    return useMutation<undefined | void, Error, AnyCriterionState[]>(
        async (criteria: AnyCriterionState[]) => {
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
            onError: (error: Error, variables: AnyCriterionState[]) => {
                onError();
            },
            onSuccess: (data: undefined | void, variables: AnyCriterionState[]) => {
                onSuccess();
            },
        }
    );
};
