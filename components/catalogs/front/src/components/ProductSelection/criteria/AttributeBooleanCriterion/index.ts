import {AttributeBooleanCriterion, AttributeBooleanCriterionState} from './types';
import {AttributeBooleanCriterion as Component} from './AttributeBooleanCriterion';
import factory from './factory';

const Criterion: AttributeBooleanCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeBooleanCriterionState};
export default Criterion;
