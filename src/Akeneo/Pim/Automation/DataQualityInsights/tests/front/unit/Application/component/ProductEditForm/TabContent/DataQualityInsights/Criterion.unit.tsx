import React from 'react';
import {fireEvent} from '@testing-library/react';
import {
  aCriterion,
  aFamily,
  anEvaluation,
  aProduct,
  aProductModel,
  aRate,
  aVariantProduct,
  renderCriterion,
} from '../../../../../../utils';
import {
  CRITERION_DONE,
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CRITERION_NOT_APPLICABLE,
} from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';
import {ATTRIBUTE_SPELLING_CRITERION_CODE} from '@akeneo-pim-community/data-quality-insights/src/application/constant';
import {Recommendation} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights';

describe('Criterion for simple product', () => {
  test('it displays error message when status is error', () => {
    const criterion = aCriterion('a_criterion', CRITERION_ERROR);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion('a_criterion', criterion, 'an_axis', evaluation);

    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation/)
    ).toBeInTheDocument();
    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error/)
    ).toBeInTheDocument();
  });

  test('it displays appropriate message when status is in progress', () => {
    const criterion = aCriterion('a_criterion', CRITERION_IN_PROGRESS);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion('a_criterion', criterion, 'an_axis', evaluation);

    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation/)
    ).toBeInTheDocument();
    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress/)
    ).toBeInTheDocument();
  });

  test('it displays appropriate message when status is not applicable', () => {
    const criterion = aCriterion('a_criterion', CRITERION_NOT_APPLICABLE);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion('a_criterion', criterion, 'an_axis', evaluation);

    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation/)
    ).toBeInTheDocument();
    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.messages.not_applicable.message/)
    ).toBeInTheDocument();
  });

  test('it displays success message when there is no attribute to improve', () => {
    const criterionRate = aRate(100, 'A');
    const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion('a_criterion', criterion, 'an_axis', evaluation);

    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation/)
    ).toBeInTheDocument();
    expect(getByText(/akeneo_data_quality_insights.product_evaluation.messages.success.criterion/)).toBeInTheDocument();
  });

  test('it displays recommendation message when there are attributes to improve', () => {
    const criterionRate = aRate(85, 'B');
    const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate, ['an_attribute']);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion('a_criterion', criterion, 'an_axis', evaluation);

    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation/)
    ).toBeInTheDocument();
    expect(getByText('an_attribute')).toBeInTheDocument();
  });

  test('it displays custom recommendation', () => {
    const criterionRate = aRate(0, 'E');
    const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate, []);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);
    const recommendation = <Recommendation type={'to_improve'}>a_custom_recommendation</Recommendation>;

    const {getByText} = renderCriterion('a_criterion', criterion, 'enrichment', evaluation, recommendation);

    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation/)
    ).toBeInTheDocument();
    expect(getByText(/a_custom_recommendation/)).toBeInTheDocument();
  });
});

describe('Criterion for product model', () => {
  test('it displays recommendation message when there are attributes to improve', () => {
    const criterionRate = aRate(85, 'B');
    const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate, ['an_attribute']);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);
    const productModel = aProductModel();

    const {getByText} = renderCriterion(
      'a_criterion',
      criterion,
      'an_axis',
      evaluation,
      undefined,
      undefined,
      undefined,
      {
        product: productModel,
      }
    );

    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation/)
    ).toBeInTheDocument();
    expect(getByText('an_attribute')).toBeInTheDocument();
  });
});

describe('Criterion for variant product ', () => {
  test('it displays recommendation message when there are attributes to improve', () => {
    const criterionRate = aRate(85, 'B');
    const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate, ['an_attribute']);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);
    const variantProduct = aVariantProduct();

    const {getByText} = renderCriterion(
      'a_criterion',
      criterion,
      'an_axis',
      evaluation,
      undefined,
      undefined,
      undefined,
      {
        product: variantProduct,
      }
    );

    expect(
      getByText(/akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation/)
    ).toBeInTheDocument();
    expect(getByText('an_attribute')).toBeInTheDocument();
  });
});

describe('Criterion user actions', () => {
  beforeEach(() => {
    jest.resetAllMocks();
    sessionStorage.clear();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it handles the follow criterion action when it is defined and user clicks on the row', () => {
    const handleFollowCriterion = jest.fn();
    const isFollowingActive = jest.fn(() => true);

    const criterionRate = aRate(85, 'B');
    const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate, ['an_attribute']);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);
    const product = aProduct(1234);
    const family = aFamily('a_family', 4321);

    const {getByText} = renderCriterion(
      ATTRIBUTE_SPELLING_CRITERION_CODE,
      criterion,
      'an_axis',
      evaluation,
      undefined,
      handleFollowCriterion,
      isFollowingActive,
      {
        families: {
          a_family: family,
        },
        product,
        catalogContext: {
          locale: 'en_US',
        },
      }
    );

    fireEvent.click(getByText('an_attribute')); // the user can click anywhere on the row

    expect(handleFollowCriterion).toHaveBeenCalledWith(criterion, family, product, 'en_US');
  });

  test('it does not handle the follow criterion action when the action is not allowed', () => {
    const handleFollowCriterion = jest.fn();
    const isFollowingActive = jest.fn(() => false);
    const criterionRate = aRate(85, 'B');
    const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate, ['an_attribute']);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);
    const product = aProduct(1234);
    const family = aFamily('a_family', 4321);

    const {getByText} = renderCriterion(
      ATTRIBUTE_SPELLING_CRITERION_CODE,
      criterion,
      'an_axis',
      evaluation,
      undefined,
      handleFollowCriterion,
      isFollowingActive,
      {
        families: {
          a_family: family,
        },
        product,
      }
    );

    fireEvent.click(getByText('an_attribute')); // the user can click anywhere on the row

    expect(handleFollowCriterion).not.toHaveBeenCalled();
  });
});
