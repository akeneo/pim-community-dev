import {CompletenessCriterionState} from './types';
import {Operator} from '../../models/Operator';

export default (state?: Partial<CompletenessCriterionState>): CompletenessCriterionState => ({
    field: 'completeness',
    operator: state?.operator ?? Operator.EQUALS,
    value: state?.value ?? 100,
    locale: state?.locale ?? null,
    scope: state?.scope ?? null,
});
