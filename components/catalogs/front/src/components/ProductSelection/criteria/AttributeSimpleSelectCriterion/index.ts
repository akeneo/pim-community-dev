import {AttributeSimpleSelectCriterion, AttributeSimpleSelectCriterionState} from './types';
import {AttributeSimpleSelectCriterion as Component} from './AttributeSimpleSelectCriterion';
import factory from './factory';

const Criterion: AttributeSimpleSelectCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeSimpleSelectCriterionState};
export default Criterion;
