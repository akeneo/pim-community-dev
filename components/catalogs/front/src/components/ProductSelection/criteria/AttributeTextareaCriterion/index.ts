import {AttributeTextareaCriterion, AttributeTextareaCriterionState} from './types';
import {AttributeTextareaCriterion as Component} from './AttributeTextareaCriterion';
import factory from './factory';

const Criterion: AttributeTextareaCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeTextareaCriterionState};
export default Criterion;
