import {CompletenessCriterionState} from './types';
import {Operator} from '../../models/Operator';

export default (state?: Partial<CompletenessCriterionState>): CompletenessCriterionState => ({
    field: 'completeness',
    operator: state?.operator !== undefined ? state.operator : Operator.EQUALS,
    value: state?.value !== undefined ? state.value : 100,
    locale: null,
    scope: null,
});
