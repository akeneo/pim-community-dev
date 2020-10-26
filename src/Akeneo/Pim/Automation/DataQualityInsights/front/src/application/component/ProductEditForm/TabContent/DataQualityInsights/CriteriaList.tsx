import React, {FunctionComponent} from 'react';
import Criterion from './Criterion';
import Evaluation, {CriterionEvaluationResult} from '../../../../../domain/Evaluation.interface';

interface CriteriaListProps {
  axis: string;
  criteria: CriterionEvaluationResult[];
  evaluation: Evaluation;
}

const CriteriaList: FunctionComponent<CriteriaListProps> = ({axis, criteria, evaluation}) => {
  return (
    <ul>
      {criteria.map((criterionEvaluation, index) => (
        <Criterion
          key={`${axis}-${index}`}
          criterionEvaluation={criterionEvaluation}
          axis={axis}
          evaluation={evaluation}
        />
      ))}
    </ul>
  );
};

export default CriteriaList;
