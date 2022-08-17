import {CompletenessCriterion, CompletenessCriterionState} from './types';
import {CompletenessCriterion as Component} from './CompletenessCriterion';
import factory from './factory';

const Criterion: CompletenessCriterion = {
    component: Component,
    factory: factory,
};

export type {CompletenessCriterionState};
export default Criterion;
