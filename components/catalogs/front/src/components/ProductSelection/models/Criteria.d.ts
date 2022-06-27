import {StatusCriterionState} from '../criteria/StatusCriterion';
import {Criterion} from './Criterion';

export type AnyCriterionState = StatusCriterionState;

export type CriteriaState = AnyCriterionState[];

export type Criteria = Criterion<AnyCriterionState>[];
