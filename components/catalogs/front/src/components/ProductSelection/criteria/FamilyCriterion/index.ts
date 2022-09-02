import {FamilyCriterion, FamilyCriterionState} from './types';
import {FamilyCriterion as Component} from './FamilyCriterion';
import factory from './factory';

const Criterion: FamilyCriterion = {
    component: Component,
    factory: factory,
};

export type {FamilyCriterionState};
export default Criterion;
