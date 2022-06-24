import {useState} from 'react';
import {Criteria} from '../../ProductSelection/models/Criteria';
import {useCatalogCriteriaState} from './useCatalogCriteriaState';
import {stateToCriterion} from '../../ProductSelection/criteria/stateToCriterion';

type LoadingResult = [undefined, () => void];
type InitializedResult = [Criteria, (criteria: Criteria) => void];
type Result = LoadingResult | InitializedResult;

const loading: LoadingResult = [undefined, () => null];

export const useEditableCatalogCriteria = (id: string): Result => {
    const backendState = useCatalogCriteriaState(id);
    const [criteria, setCriteria] = useState<Criteria | undefined>(undefined);

    console.log(backendState, criteria);

    if (backendState.isLoading) {
        return loading;
    }

    if (backendState.isError || undefined === backendState.data) {
        throw new Error('Unable to initialize editable catalog criteria from the backend state');
    }

    if (undefined === criteria) {
        const backendCriteria = backendState.data.map(state => stateToCriterion(state));

        setCriteria(backendCriteria);

        return loading;
    }

    return [criteria, setCriteria];
};
