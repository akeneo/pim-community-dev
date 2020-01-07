import React, {FunctionComponent} from 'react';
import styled from "styled-components";

import RecommendationAttributesList from "./RecommendationAttributesList";
import Attribute from './Attribute';
import {Rate, RANK_1, Recommendation} from "../../../../../domain";

const __ = require('oro/translator');

interface CriterionProps {
  recommendation: Recommendation;
  rate?: Rate;
}

const isSuccess = (rate?: Rate) => {
  return rate && rate.letterRate  === RANK_1;
};

const Message = styled.div`
  line-height: normal;
`;

const Criterion: FunctionComponent<CriterionProps> = ({recommendation, rate}) => {
  const criterion = recommendation.criterion as string;
  const attributes = recommendation.attributes || [];

  return (
    <li className="AknVerticalList-item">
      <Message>
        <span>
          {__(`akeneo_data_quality_insights.product_evaluation.criteria.${criterion}.recommendation`)}:&nbsp;
        </span>
        <span>
          {isSuccess(rate) ? (
            <Attribute isClickable={false} code={''}>
              {__(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
            </Attribute>
          ) : (
            <RecommendationAttributesList criterion={criterion} attributes={attributes}/>
          )}
        </span>
      </Message>
    </li>
  );
};

export default Criterion;
