import React, {FunctionComponent} from 'react';
import {uniq as _uniq} from 'lodash';

import Rate from "../../../Rate";
import AllAttributesLink from "./AllAttributesLink";
import {Evaluation, RANK_1, Recommendation} from "../../../../../domain";
import CriteriaList from "./CriteriaList";
import AxisError from "./AxisError";

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


const AxisEvaluation: FunctionComponent<AxisEvaluationProps> = ({evaluation, axis}) => {
  const recommendations = evaluation.recommendations || [];
  const rates = evaluation.rates || [];
  const allAttributes = getAllAttributes(recommendations);

  return (
    <div className='AknSubsection AxisEvaluationContainer'>
      <header className="AknSubsection-title">
        <span className="group-label">
          <span className='AxisEvaluationTitle'>{axis}</span>
          <Rate value={evaluation.rate} />
        </span>
        <span>
          {canDisplayAllAttributesLink(evaluation, allAttributes) && (
            <AllAttributesLink axis={axis} attributes={allAttributes}/>
          )}
        </span>
      </header>
      { evaluation.rate == "" ? (
        <AxisError/>
      ) : (
        <></>
      )}
      <CriteriaList axis={axis} recommendations={recommendations} rates={rates}/>
    </div>
  )
};

export default AxisEvaluation;
