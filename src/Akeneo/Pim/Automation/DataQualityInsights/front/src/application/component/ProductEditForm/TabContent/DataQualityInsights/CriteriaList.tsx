import React, {FunctionComponent} from 'react';
import Criterion from "./Criterion";
import {CriterionEvaluationResult} from "../../../../../domain/Evaluation.interface";

interface CriteriaListProps {
  axis: string;
  criteria: CriterionEvaluationResult[];
}

const CriteriaList: FunctionComponent<CriteriaListProps> = ({axis, criteria}) => {
  return (
    <ul>
      {criteria.map((criterionEvaluation, index) => (
        <Criterion key={`${axis}-${index}`} evaluation={criterionEvaluation}/>
      ))}
    </ul>
  );
};

export default CriteriaList;
