import React, {FC} from 'react';

const translate = require('oro/translator');

type RecommendationType = 'error' | 'success' | 'in_progress' | 'not_applicable' | 'to_improve';

type Props = {
  type: RecommendationType;
};

const Recommendation: FC<Props> = ({children, type}) => {
  if (type === 'error') {
    return (
      <span className="CriterionErrorMessage">
        {children || translate(`akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error`)}
      </span>
    );
  }

  if (type === 'in_progress') {
    return (
      <span className="CriterionInProgressMessage">
        {children || translate(`akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress`)}
      </span>
    );
  }

  if (type === 'not_applicable') {
    return <span className="NotApplicableAttribute">{children || 'N/A'}</span>;
  }

  if (type === 'success') {
    return (
      <span className="CriterionSuccessMessage">
        {children || translate(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
      </span>
    );
  }

  return <>{children}</>;
};

export {Recommendation, RecommendationType};
