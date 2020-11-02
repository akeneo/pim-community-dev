import React, {FunctionComponent} from 'react';
import {uniq as _uniq} from 'lodash';

import Rate from '@akeneo-pim-community/data-quality-insights/src/application/component/Rate';
import AllAttributesLink from './AllAttributesLink';
import {Evaluation} from '../../../../../domain';
import CriteriaList from './CriteriaList';
import AxisError from './AxisError';
import {
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CriterionEvaluationResult,
} from '../../../../../domain/Evaluation.interface';
import AxisGradingInProgress from './AxisGradingInProgress';

const __ = require('oro/translator');

interface AxisEvaluationProps {
  evaluation: Evaluation;
  axis: string;
}

const getAxisAttributesWithRecommendations = (criteria: CriterionEvaluationResult[]): string[] => {
  let attributes: string[] = [];

  criteria.map(criterion => {
    attributes = [...criterion.improvable_attributes, ...attributes];
  });

  return _uniq(attributes);
};

const canDisplayAllAttributesLink = (attributes: string[]) => {
  return attributes.length > 0;
};

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

const AxisEvaluation: FunctionComponent<AxisEvaluationProps> = ({evaluation, axis}) => {
  const criteria = evaluation.criteria || [];
  const allAttributes = getAxisAttributesWithRecommendations(criteria);
  const axisHasError: boolean = isAxisInError(criteria);
  const axisGradingInProgress: boolean = isAxisGradingInProgress(criteria);

  return (
    <div className="AknSubsection AxisEvaluationContainer">
      <header className="AknSubsection-title">
        <span className="group-label">
          <span className="AxisEvaluationTitle">
            {__(`akeneo_data_quality_insights.product_evaluation.axis.${axis}.title`)}
          </span>
          <Rate value={evaluation.rate ? evaluation.rate.rank : null} />
        </span>
        <span>
          {canDisplayAllAttributesLink(allAttributes) && <AllAttributesLink axis={axis} attributes={allAttributes} />}
        </span>
      </header>

      {axisHasError && <AxisError />}
      {axisGradingInProgress && !axisHasError && <AxisGradingInProgress />}

      <CriteriaList axis={axis} criteria={criteria} evaluation={evaluation} />
    </div>
  );
};

export default AxisEvaluation;
