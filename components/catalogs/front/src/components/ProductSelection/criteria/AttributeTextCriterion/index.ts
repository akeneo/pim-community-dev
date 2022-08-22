import {AttributeTextCriterion, AttributeTextCriterionState} from './types';
import {AttributeTextCriterion as Component} from './AttributeTextCriterion';
import factory from './factory';

const Criterion: AttributeTextCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeTextCriterionState};
export default Criterion;
