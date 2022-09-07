import {AttributeMultiSelectCriterion, AttributeMultiSelectCriterionState} from './types';
import {AttributeMultiSelectCriterion as Component} from './AttributeMultiSelectCriterion';
import factory from './factory';

const Criterion: AttributeMultiSelectCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeMultiSelectCriterionState};
export default Criterion;
