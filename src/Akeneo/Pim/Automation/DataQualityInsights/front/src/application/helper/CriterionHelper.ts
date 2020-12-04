import {CriterionEvaluationResult} from '../../domain';

const criterionPlaceholder: CriterionEvaluationResult = {
  rate: {
    value: null,
    rank: null,
  },
  code: '',
  status: 'not_applicable',
  improvable_attributes: [],
};

export {criterionPlaceholder};
