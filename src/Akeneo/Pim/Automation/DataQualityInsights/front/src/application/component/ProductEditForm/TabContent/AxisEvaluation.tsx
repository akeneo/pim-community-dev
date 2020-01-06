import React, {FunctionComponent} from 'react';
import styled from 'styled-components';
import {uniq as _uniq} from 'lodash';

import Rate from "../../Rate";
import AllAttributesLink from "./AllAttributesLink";
import {Evaluation, RANK_1, Recommendation} from "../../../../domain";
import CriteriaList from "./CriteriaList";
import AxisEvaluationSucces from "./AxisEvaluationSuccess";

interface AxisEvaluationProps {
  evaluation: Evaluation;
  axis: string;
}

const getAllAttributes = (recommendations: Recommendation[]): string[] => {
  let  attributes: string[] = [];

  recommendations.map((recommendation) => {
    attributes = [
      ...recommendation.attributes,
      ...attributes,
    ];
  });

  return _uniq(attributes);
};

const isSuccess = (axis: Evaluation) => {
  return (axis.rate === RANK_1)
};

const canDisplayAllAttributesLink = (axis: Evaluation, attributes: string[]) => {
  return (!isSuccess(axis) && attributes.length > 0);
};

const Title = styled.span`
  display: inline-block;
  margin-right: 10px;
`;

const Container = styled.div`
  margin-bottom: 20px;
`;

const AxisEvaluation: FunctionComponent<AxisEvaluationProps> = ({evaluation, axis}) => {
  const recommendations = evaluation.recommendations || [];
  const rates = evaluation.rates || [];
  const allAttributes = getAllAttributes(recommendations);

  return (
    <Container className='AknSubsection'>
      <header className="AknSubsection-title">
        <span className="group-label">
          <Title>{axis}</Title>
          <Rate value={evaluation.rate} />
        </span>
        <span>
          {canDisplayAllAttributesLink(evaluation, allAttributes) && (
            <AllAttributesLink axis={axis} attributes={allAttributes}/>
          )}
        </span>
      </header>
      {isSuccess(evaluation) ? (
        <AxisEvaluationSucces axis={axis}/>
      ) : (
        <CriteriaList axis={axis} recommendations={recommendations} rates={rates}/>
      )}
    </Container>
  )
};

export default AxisEvaluation;
