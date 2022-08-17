import {StatusCriterionState} from './types';
import {Operator} from '../../models/Operator';

export default (state?: Partial<StatusCriterionState>): StatusCriterionState => ({
    field: 'enabled',
    operator: state?.operator ?? Operator.EQUALS,
    value: state?.value ?? true,
});
