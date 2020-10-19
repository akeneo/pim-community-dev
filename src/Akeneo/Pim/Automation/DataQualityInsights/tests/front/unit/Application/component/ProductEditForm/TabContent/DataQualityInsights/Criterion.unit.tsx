import React, {FC} from 'react';

import Criterion from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {
    aCriterion,
    aFamily,
    anAttribute,
    anEvaluation,
    aRate,
    renderWithAppContextHelper
} from '../../../../../../utils';
import Evaluation, {
  CRITERION_DONE,
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CRITERION_NOT_APPLICABLE,
  CriterionEvaluationResult,
} from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';

const renderCriterion = (
  criterionEvaluation: CriterionEvaluationResult,
  axis: string,
  evaluation: Evaluation,
  appState = {}
) => {
  const Component: FC = () => (
    <AxesContextProvider axes={[axis]}>
      <Criterion criterionEvaluation={criterionEvaluation} axis={axis} evaluation={evaluation} />
    </AxesContextProvider>
  );

  return renderWithAppContextHelper(<Component />, appState);
};

describe('Criterion', () => {

    test('it displays error message when status is error', () => {
        const criterion = aCriterion('a_criterion', CRITERION_ERROR);
        const rate = aRate();
        const evaluation = anEvaluation(rate, [criterion]);

        const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

        expect(getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')).toBeInTheDocument();
        expect(getByText('akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error')).toBeInTheDocument();
    });

    test('it displays appropriate message when status is in progress', () => {
        const criterion = aCriterion('a_criterion', CRITERION_IN_PROGRESS);
        const rate = aRate();
        const evaluation = anEvaluation(rate, [criterion]);

        const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

        expect(getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')).toBeInTheDocument();
        expect(getByText('akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress')).toBeInTheDocument();
    });

    test('it displays appropriate message when status is not applicable', () => {
        const criterion = aCriterion('a_criterion', CRITERION_NOT_APPLICABLE);
        const rate = aRate();
        const evaluation = anEvaluation(rate, [criterion]);

        const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

        expect(getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')).toBeInTheDocument();
        expect(getByText('N/A')).toBeInTheDocument();
    });

    test('it displays success message when there is no attribute to improve', () => {
        const criterionRate = aRate(100, 'A');
        const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate);
        const rate = aRate();
        const evaluation = anEvaluation(rate, [criterion]);

        const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

        expect(getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')).toBeInTheDocument();
        expect(getByText('akeneo_data_quality_insights.product_evaluation.messages.success.criterion')).toBeInTheDocument();
    });

    test('it displays recommendation message when there are attributes to improve', () => {
        const criterionRate = aRate(85, 'B');
        const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate, ['an_attribute']);
        const rate = aRate();
        const evaluation = anEvaluation(rate, [criterion]);

        const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

        expect(getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')).toBeInTheDocument();
        expect(getByText('an_attribute')).toBeInTheDocument();
    });
});
