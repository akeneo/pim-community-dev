import {CriterionEvaluationResult} from '@akeneo-pim-community/data-quality-insights/src';
import Evaluation, {Status} from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';
import {Rate} from '@akeneo-pim-community/data-quality-insights/src/domain';

const aRate = (value: number | null = null, rank: string | null = null): Rate => {
  return {
    value,
    rank,
  };
};

const aCriterion = (
  code: string = 'a_criterion',
  status: Status = 'done',
  rate: Rate | null = null,
  improvable_attributes: string[] = []
): CriterionEvaluationResult => {
  return {
    code,
    rate: rate === null ? aRate(null, null) : rate,
    status,
    improvable_attributes,
  };
};

const anEvaluation = (rate: Rate | null = null, criteria: CriterionEvaluationResult[] = []): Evaluation => {
  return {
    rate: rate === null ? aRate(null, null) : rate,
    criteria,
  };
};

export {aCriterion, anEvaluation, aRate};
