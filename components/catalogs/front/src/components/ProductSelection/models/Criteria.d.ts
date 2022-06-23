import {StatusCriterionState} from '../criteria/StatusCriterion';

export type AnyCriterionState = StatusCriterionState;

export type CriteriaState = AnyCriterionState[];

export type Criteria = Criterion<StatusCriterionState>[];
