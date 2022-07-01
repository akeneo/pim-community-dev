import {Criterion, CriterionState} from '../models/Criterion';
import StatusCriterion, {StatusCriterionState} from './StatusCriterion';
import FamilyCriterion, {FamilyCriterionState} from './FamilyCriterion';

export const stateToCriterion = (state: CriterionState): Criterion<any> => {
    switch (state.field) {
        case 'enabled':
            return StatusCriterion(state as StatusCriterionState);
        case 'family':
            return FamilyCriterion(state as FamilyCriterionState);
        default:
            throw new Error('Not implemented criterion');
    }
};
