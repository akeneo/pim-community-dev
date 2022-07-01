import {FamilyCriterion, FamilyCriterionState} from './types';
import {FamilyCriterion as Component} from './FamilyCriterion';
import {Operator} from '../../models/Operator';

export type {FamilyCriterionState};

export default (state?: Partial<FamilyCriterionState>): FamilyCriterion => ({
    id: (Math.random() + 1).toString(36).substring(7),
    module: Component,
    state: {
        field: 'family',
        operator: state?.operator !== undefined ? state.operator : Operator.IS_NOT_EMPTY,
        value: state?.value !== undefined ? state.value : [],
    },
});
