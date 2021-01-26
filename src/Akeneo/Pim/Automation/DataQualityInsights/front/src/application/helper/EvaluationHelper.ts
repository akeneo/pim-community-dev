import {CriterionEvaluationResult, Evaluation, ProductEvaluation} from '../../domain';

const evaluationPlaceholder: Evaluation = {
  rate: {
    value: null,
    rank: null,
  },
  criteria: [],
};

type NewProductEvaluation = {
  [channel: string]: {
    [locale: string]: CriterionEvaluationResult[];
  };
};

const convertEvaluationToLegacyFormat = (
  axes: {[axis: string]: string[]},
  productEvaluation: NewProductEvaluation
): ProductEvaluation => {
  let result: ProductEvaluation = {};
  Object.keys(axes).forEach((axis: string) => {
    Object.entries(productEvaluation).forEach(([channel, criteriaByLocale]) => {
      Object.entries(criteriaByLocale).forEach(([locale, criteria]) => {
        const filteredCriteria: CriterionEvaluationResult[] = criteria.filter(criterion =>
          axes[axis].includes(criterion.code)
        );
        const resultByAxis = result[axis] || {};
        const resultByChannel = resultByAxis[channel] || {};
        result = {
          ...result,
          [axis]: {
            ...resultByAxis,
            [channel]: {
              ...resultByChannel,
              [locale]: {
                rate: null,
                criteria: filteredCriteria,
              },
            },
          },
        };
      });
    });
  });

  return result;
};

export {evaluationPlaceholder, convertEvaluationToLegacyFormat, NewProductEvaluation};
