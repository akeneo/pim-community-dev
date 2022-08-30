import {AttributeNumberCriterion, AttributeNumberCriterionState} from './types';
import {AttributeNumberCriterion as Component} from './AttributeNumberCriterion';
import factory from './factory';

const Criterion: AttributeNumberCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeNumberCriterionState};
export default Criterion;
