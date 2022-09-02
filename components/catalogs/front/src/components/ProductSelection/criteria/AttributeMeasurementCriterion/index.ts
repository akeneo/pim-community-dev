import {AttributeMeasurementCriterion, AttributeMeasurementCriterionState} from './types';
import {AttributeMeasurementCriterion as Component} from './AttributeMeasurementCriterion';
import factory from './factory';

const Criterion: AttributeMeasurementCriterion = {
    component: Component,
    factory: factory,
};

export type {AttributeMeasurementCriterionState};
export default Criterion;
