import React, {FC} from 'react';
import {CriterionEvaluationResult} from '../../../../../../domain';

// @todo[DAPI-1339] use the "useTranslate" hook from legacy-bridge workspace
const translate = require('oro/translator');

enum RecommendationType {
  ERROR = 'error',
  SUCCESS = 'success',
  IN_PROGRESS = 'in_progress',
  NOT_APPLICABLE = 'not_applicable',
  OTHER = 'other,',
}

type SupportsRecommendationHandler = (criterion: CriterionEvaluationResult) => boolean;

type Props = {
  type?: RecommendationType;
  supports?: SupportsRecommendationHandler;
};

const Recommendation: FC<Props> = ({children, type = RecommendationType.OTHER}) => {
  if (type === RecommendationType.ERROR) {
    return (
      <span className="CriterionErrorMessage">
        {translate(`akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error`)}
      </span>
    );
  }

  if (type === RecommendationType.IN_PROGRESS) {
    return (
      <span className="CriterionInProgressMessage">
        {translate(`akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress`)}
      </span>
    );
  }

  if (type === RecommendationType.NOT_APPLICABLE) {
    return <span className="NotApplicableAttribute">N/A</span>;
  }

  if (type === RecommendationType.SUCCESS) {
    return (
      <span className="CriterionSuccessMessage">
        {translate(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
      </span>
    );
  }

  return <>{children}</>;
};

export {Recommendation, RecommendationType};
