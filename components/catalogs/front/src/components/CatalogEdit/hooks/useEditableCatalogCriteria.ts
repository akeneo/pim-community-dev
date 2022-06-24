import {useState} from 'react';
import {Criteria} from '../../ProductSelection/models/Criteria';
import {useCatalogCriteriaState} from './useCatalogCriteriaState';
import {stateToCriterion} from '../../ProductSelection/criteria/stateToCriterion';

type LoadingResult = [undefined, undefined];
type InitializedResult = [Criteria, (criteria: Criteria) => void];
type Result = LoadingResult | InitializedResult;

const loading: LoadingResult = [undefined, undefined];

export const useEditableCatalogCriteria = (id: string): Result => {
    const backendState = useCatalogCriteriaState(id);
    const [criteria, setCriteria] = useState<Criteria | undefined>(undefined);

    if (backendState.isLoading) {
        return loading;
    }

    if (backendState.isError || undefined === backendState.data) {
        throw Error('Unable to initialize editable catalog criteria from the backend state');
    }

    if (undefined === criteria) {
        const backendCriteria = backendState.data.map(state => stateToCriterion(state));

        setCriteria(backendCriteria);

        return loading;
    }

    return [criteria, setCriteria];
};
