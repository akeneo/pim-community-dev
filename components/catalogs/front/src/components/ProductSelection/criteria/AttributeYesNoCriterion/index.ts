import {AttributeYesNoCriterion, AttributeYesNoCriterionState} from './types';
import {AttributeYesNoCriterion as Component} from './AttributeYesNoCriterion';
import factory from './factory';

const Criterion: AttributeYesNoCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeYesNoCriterionState};
export default Criterion;
