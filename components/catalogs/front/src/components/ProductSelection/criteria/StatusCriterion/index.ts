import {StatusCriterion, StatusCriterionState} from './types';
import {StatusCriterion as Component} from './StatusCriterion';
import {Operator} from '../../models/Operator';

const Criterion: StatusCriterion = {
    component: Component,
    factory: (state?: Partial<StatusCriterionState>): StatusCriterionState => ({
        field: 'enabled',
        operator: state?.operator !== undefined ? state.operator : Operator.EQUALS,
        value: state?.value !== undefined ? state.value : true,
    }),
};

export type {StatusCriterionState};
export default Criterion;
