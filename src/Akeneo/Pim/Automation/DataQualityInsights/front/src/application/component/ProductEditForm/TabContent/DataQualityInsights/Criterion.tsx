import React, {FunctionComponent} from 'react';

import RecommendationAttributesList from "./RecommendationAttributesList";
import {MAX_RATE, Rate} from "../../../../../domain";
import Evaluation, {
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS, CRITERION_NOT_APPLICABLE,
  CriterionEvaluationResult
} from "../../../../../domain/Evaluation.interface";
import {useProduct} from "../../../../../infrastructure/hooks";
import {isSimpleProduct} from "../../../../helper/ProductEditForm/Product";

const __ = require('oro/translator');

interface CriterionProps {
  criterionEvaluation: CriterionEvaluationResult;
  axis: string;
  evaluation: Evaluation;
}

const isSuccess = (rate: Rate) => {
  return rate && rate.value === MAX_RATE;
};

const Criterion: FunctionComponent<CriterionProps> = ({criterionEvaluation, axis, evaluation}) => {
  const product = useProduct();

  const criterion = criterionEvaluation.code;
  const attributes = criterionEvaluation.improvable_attributes || [] as string[];

  let criterionContent: any;
  if(criterionEvaluation.status === CRITERION_ERROR) {
    criterionContent =
      <span className="CriterionErrorMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error`)}
      </span>;
  } else if(criterionEvaluation.status === CRITERION_IN_PROGRESS) {
    criterionContent =
      <span className="CriterionInProgressMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress`)}
      </span>;
  } else if(criterionEvaluation.status === CRITERION_NOT_APPLICABLE) {
    criterionContent =
      <span className="NotApplicableAttribute">N/A</span>;
  } else if(isSuccess(criterionEvaluation.rate) && attributes.length == 0) {
    criterionContent =
      <div className="CriterionSuccessContainer">
        <span className="CriterionSuccessMessage">
          {__(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
        </span>
        <span className="CriterionSuccessTick"/>
      </div>;
  } else {
    criterionContent = <RecommendationAttributesList criterion={criterion} attributes={attributes} product={product} axis={axis} evaluation={evaluation}/>;
  }

  return (
    <li className="AknVerticalList-item" data-testid={"dqiProductEvaluationCriterion"}>
      <div className={`CriterionMessage ${!isSimpleProduct(product) ? 'CriterionMessage--Variant' : ''}`}>
        <span className="CriterionRecommendationMessage">
          {__(`akeneo_data_quality_insights.product_evaluation.criteria.${criterion}.recommendation`)}:&nbsp;
        </span>

        {criterionContent}

      </div>
    </li>
  );
};

export default Criterion;
