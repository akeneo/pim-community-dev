import {FamilyCriterion, FamilyCriterionState} from './types';
import {FamilyCriterion as Component} from './FamilyCriterion';
import {Operator} from '../../models/Operator';

const Criterion: FamilyCriterion = {
    component: Component,
    factory: (state?: Partial<FamilyCriterionState>): FamilyCriterionState => ({
        field: 'family',
        operator: state?.operator !== undefined ? state.operator : Operator.IS_NOT_EMPTY,
        value: state?.value !== undefined ? state.value : [],
    }),
};

export type {FamilyCriterionState};
export default Criterion;
