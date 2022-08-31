import {CategoryCriterionState} from './types';
import {Operator} from '../../models/Operator';

export default (state?: Partial<CategoryCriterionState>): CategoryCriterionState => ({
    field: 'categories',
    operator: state?.operator ?? Operator.IN_LIST,
    value: state?.value ?? [],
});
