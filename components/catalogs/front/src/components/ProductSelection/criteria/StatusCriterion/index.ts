import {StatusCriterion, StatusCriterionState} from './types';
import {StatusCriterion as Component} from './StatusCriterion';
import {Operator} from '../../models/Operator';

export default (state?: StatusCriterionState): StatusCriterion => ({
    id: (Math.random() + 1).toString(36).substring(7),
    module: Component,
    state: {
        field: 'status',
        operator: state?.operator !== undefined ? state.operator : Operator.EQUALS,
        value: state?.value !== undefined ? state.value : true,
    },
});
