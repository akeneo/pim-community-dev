import {Criterion, CriterionState} from '../models/Criterion';
import StatusCriterion, {StatusCriterionState} from './StatusCriterion';
import {AnyCriterionState} from '../models/Criteria';

export const getCriterionByFieldType = (field: string): Criterion<AnyCriterionState> => {
    switch (field) {
        case 'enabled':
            return StatusCriterion;
        default:
            throw new Error('Not implemented criterion');
    }
};
