import {CategoryCriterion, CategoryCriterionState} from './types';
import {CategoryCriterion as Component} from './CategoryCriterion';
import factory from './factory';

const Criterion: CategoryCriterion = {
    component: Component,
    factory: factory,
};

export type {CategoryCriterionState};
export default Criterion;
