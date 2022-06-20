import {StatusCriterionState} from '../criteria/StatusCriterion';
import {Criterion} from './Criterion';

export type CriterionStates = StatusCriterionState;
export type Criteria = Criterion<CriterionStates>[];
