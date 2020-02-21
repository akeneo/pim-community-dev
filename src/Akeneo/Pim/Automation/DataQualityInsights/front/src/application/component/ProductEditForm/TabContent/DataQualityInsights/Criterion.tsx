import React, {FunctionComponent} from 'react';

import RecommendationAttributesList from "./RecommendationAttributesList";
import {MAX_RATE, Rate} from "../../../../../domain";
import {
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS, CRITERION_NOT_APPLICABLE,
  CriterionEvaluationResult
} from "../../../../../domain/Evaluation.interface";

const __ = require('oro/translator');

interface CriterionProps {
  evaluation: CriterionEvaluationResult;
}

const isSuccess = (rate: Rate) => {
  return rate && rate.value === MAX_RATE;
};

const Criterion: FunctionComponent<CriterionProps> = ({evaluation}) => {
  const criterion = evaluation.code;
  const attributes = evaluation.improvable_attributes || [] as string[];

  let criterionContent = <RecommendationAttributesList criterion={criterion} attributes={attributes}/>;
  if(evaluation.status === CRITERION_ERROR) {
    criterionContent =
      <span className="CriterionErrorMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error`)}
      </span>;
  } else if(evaluation.status === CRITERION_IN_PROGRESS) {
    criterionContent =
      <span className="CriterionInProgressMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress`)}
      </span>;
  } else if(evaluation.status === CRITERION_NOT_APPLICABLE) {
    criterionContent =
      <span className="NotApplicableAttribute">N/A</span>;
  } else if(isSuccess(evaluation.rate) && attributes.length == 0) {
    criterionContent =
      <div className="CriterionSuccessContainer">
        <span className="CriterionSuccessMessage">
          {__(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
        </span>
        <span className="CriterionSuccessTick"/>
      </div>;
  }

  return (
    <li className="AknVerticalList-item" data-testid={"dqiProductEvaluationCriterion"}>
      <div className="CriterionMessage">
        <span className="CriterionRecommendationMessage">
          {__(`akeneo_data_quality_insights.product_evaluation.criteria.${criterion}.recommendation`)}:&nbsp;
        </span>

        {criterionContent}

      </div>
    </li>
  );
};

export default Criterion;
