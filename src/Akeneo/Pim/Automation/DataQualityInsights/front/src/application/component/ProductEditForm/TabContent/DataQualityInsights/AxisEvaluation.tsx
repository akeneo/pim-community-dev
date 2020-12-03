import React, {Children, FC, ReactElement} from 'react';
import {Evaluation} from '../../../../../domain';
import {AxisError, AxisGradingInProgress, AxisHeader} from './Axis';
import {
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CriterionEvaluationResult,
} from '../../../../../domain/Evaluation.interface';
import {Criterion} from './Criterion';
import {evaluationPlaceholder} from '../../../../helper';

interface AxisEvaluationProps {
  evaluation?: Evaluation;
  axis: string;
}

const isAxisInError = (criteria: CriterionEvaluationResult[]) => {
  return (
    criteria.filter((criterionEvaluation: CriterionEvaluationResult) => criterionEvaluation.status === CRITERION_ERROR)
      .length > 0
  );
};

const isAxisGradingInProgress = (criteria: CriterionEvaluationResult[]) => {
  return (
    criteria.filter(
      (criterionEvaluation: CriterionEvaluationResult) => criterionEvaluation.status === CRITERION_IN_PROGRESS
    ).length > 0
  );
};

const AxisEvaluation: FC<AxisEvaluationProps> = ({children, evaluation = evaluationPlaceholder, axis}) => {
  const criteria = evaluation.criteria || [];
  const axisHasError: boolean = isAxisInError(criteria);
  const axisGradingInProgress: boolean = isAxisGradingInProgress(criteria);

  const getCriterionEvaluation = (code: string): CriterionEvaluationResult | undefined => {
    return criteria.find(criterion => criterion.code === code);
  };

  return (
    <div className="AknSubsection AxisEvaluationContainer">
      <AxisHeader evaluation={evaluation} axis={axis} />

      {axisHasError && <AxisError />}
      {axisGradingInProgress && !axisHasError && <AxisGradingInProgress />}

      {Children.map(children, child => {
        const element = child as ReactElement;
        if (element.type === Criterion) {
          const criterionEvaluation = getCriterionEvaluation(element.props.code);

          if (!criterionEvaluation) {
            return;
          }

          return React.cloneElement(element, {
            axis,
            evaluation,
            criterionEvaluation,
          });
        }
        return child;
      })}
    </div>
  );
};

export default AxisEvaluation;
