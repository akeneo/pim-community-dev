import {StatusCriterionState} from '../criteria/StatusCriterion';
import {Criterion} from './Criterion';
import {FamilyCriterionState} from '../criteria/FamilyCriterion';

export type AnyCriterionState = StatusCriterionState | FamilyCriterionState;

export type CriteriaState = AnyCriterionState[];

export type Criteria = Criterion<AnyCriterionState>[];
