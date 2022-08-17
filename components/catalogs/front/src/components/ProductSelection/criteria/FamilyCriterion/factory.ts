import {FamilyCriterionState} from './types';
import {Operator} from '../../models/Operator';

export default (state?: Partial<FamilyCriterionState>): FamilyCriterionState => ({
    field: 'family',
    operator: state?.operator ?? Operator.IN_LIST,
    value: state?.value ?? [],
});
