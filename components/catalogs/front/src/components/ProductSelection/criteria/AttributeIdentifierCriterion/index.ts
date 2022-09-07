import {AttributeIdentifierCriterion, AttributeIdentifierCriterionState} from './types';
import {AttributeIdentifierCriterion as Component} from './AttributeIdentifierCriterion';
import factory from './factory';

const Criterion: AttributeIdentifierCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeIdentifierCriterionState};
export default Criterion;
