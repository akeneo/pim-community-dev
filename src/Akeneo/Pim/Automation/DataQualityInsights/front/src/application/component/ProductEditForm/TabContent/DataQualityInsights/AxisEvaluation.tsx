import React, {FC} from 'react';
import {Evaluation} from '../../../../../domain';
import CriteriaList from './CriteriaList';
import {AxisError, AxisGradingInProgress, AxisHeader} from './Axis';
import {
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CriterionEvaluationResult,
} from '../../../../../domain/Evaluation.interface';

interface AxisEvaluationProps {
  evaluation?: Evaluation;
  axis: string;
}

const isAxisInError = (criteria: CriterionEvaluationResult[]) => {
  return criteria
    .filter((criterionEvaluation: CriterionEvaluationResult) => criterionEvaluation.status === CRITERION_ERROR)
    .length > 0;
};

const isAxisGradingInProgress = (criteria: CriterionEvaluationResult[]) => {
  return criteria
    .filter((criterionEvaluation: CriterionEvaluationResult) => criterionEvaluation.status === CRITERION_IN_PROGRESS)
    .length > 0;
};

const defaultEvaluation: Evaluation = {
  rate: {
    value: null,
    rank: null,
  },
  criteria: [],
};

const AxisEvaluation: FC<AxisEvaluationProps> = ({evaluation = defaultEvaluation, axis}) => {
  const criteria = evaluation.criteria || [];
  const axisHasError: boolean = isAxisInError(criteria);
  const axisGradingInProgress: boolean = isAxisGradingInProgress(criteria);

  return (
    <div className='AknSubsection AxisEvaluationContainer'>
      <AxisHeader evaluation={evaluation} axis={axis}/>

      { axisHasError && (<AxisError/>) }
      { axisGradingInProgress && !axisHasError && (<AxisGradingInProgress/>) }

      <CriteriaList axis={axis} criteria={criteria} evaluation={evaluation}/>
    </div>
  )
};

export default AxisEvaluation;
