import {StatusCriterion, StatusCriterionState} from './types';
import {StatusCriterion as Component} from './StatusCriterion';
import factory from './factory';

const Criterion: StatusCriterion = {
    component: Component,
    factory: factory,
};

export type {StatusCriterionState};
export default Criterion;
