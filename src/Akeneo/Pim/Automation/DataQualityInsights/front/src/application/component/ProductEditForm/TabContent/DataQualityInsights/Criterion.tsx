import React, {FunctionComponent} from 'react';

import RecommendationAttributesList from "./RecommendationAttributesList";
import {Rate, MAX_RATE, Recommendation} from "../../../../../domain";

const __ = require('oro/translator');

interface CriterionProps {
  recommendation: Recommendation;
  rate?: Rate;
}

const isSuccess = (rate?: Rate) => {
  return rate && rate.rate === MAX_RATE;
};

const Criterion: FunctionComponent<CriterionProps> = ({recommendation, rate}) => {
  const criterion = recommendation.criterion as string;
  const attributes = recommendation.attributes || [] as string[];

  return (
    <li className="AknVerticalList-item">
      <div className="CriterionMessage">
        <span className="CriterionRecommendationMessage">
          {__(`akeneo_data_quality_insights.product_evaluation.criteria.${criterion}.recommendation`)}:&nbsp;
        </span>
          {isSuccess(rate) && attributes.length == 0 ? (
              <div className="CriterionSuccessContainer">
                <span className="CriterionSuccessMessage">
                    {__(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
                </span>
                <span className="CriterionSuccessTick"/>
              </div>
          ) : (
            <RecommendationAttributesList criterion={criterion} attributes={attributes}/>
          )}
      </div>
    </li>
  );
};

export default Criterion;
