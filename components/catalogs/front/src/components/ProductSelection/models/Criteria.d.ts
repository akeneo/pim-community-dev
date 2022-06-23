import {StatusCriterionState} from '../criteria/StatusCriterion';
import {CriterionState} from './Criterion';

export type Criteria = Criterion<StatusCriterionState>[];

export type CriteriaState = AnyCriterionState[];

export type AnyCriterionState = StatusCriterionState | CriterionState;
