import {Criterion, CriterionState} from '../models/Criterion';
import StatusCriterion, {StatusCriterionState} from './StatusCriterion';
import {AnyCriterionState} from '../models/Criteria';

export const stateToCriterion = (state: CriterionState): Criterion<any> => {
    switch (state.field) {
        case 'enabled':
            return StatusCriterion(state as StatusCriterionState);
        default:
            throw new Error('Not implemented criterion');
    }
};
