import {Rate} from '@akeneo-pim-community/data-quality-insights/src/domain';

export interface ProductEvaluation {
  [axis: string]: AxisEvaluation;
}

export interface AxisEvaluation {
  [channel: string]: {
    [locale: string]: Evaluation;
  };
}

export default interface Evaluation {
  rate: Rate | null;
  criteria: CriterionEvaluationResult[];
}

export const CRITERION_IN_PROGRESS = 'in_progress';
export const CRITERION_DONE = 'done';
export const CRITERION_ERROR = 'error';
export const CRITERION_NOT_APPLICABLE = 'not_applicable';

export interface CriterionEvaluationResult {
  code: string;
  rate: Rate;
  status: Status;
  improvable_attributes: string[];
}

export type Status = 'in_progress' | 'done' | 'error' | 'not_applicable';
