import {AttributeDateCriterion, AttributeDateCriterionState} from './types';
import {AttributeDateCriterion as Component} from './AttributeDateCriterion';
import factory from './factory';

const Criterion: AttributeDateCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeDateCriterionState};
export default Criterion;
