import {
  aCriterion,
  anEvaluation,
  aProductModel,
  aRate,
  aVariantProduct,
  renderCriterion
} from '../../../../../../utils';
import {
  CRITERION_DONE,
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CRITERION_NOT_APPLICABLE,
} from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';

describe('Criterion for simple product', () => {
  test('it displays error message when status is error', () => {
    const criterion = aCriterion('a_criterion', CRITERION_ERROR);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

    expect(
      getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')
    ).toBeInTheDocument();
    expect(
      getByText('akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error')
    ).toBeInTheDocument();
  });

  test('it displays appropriate message when status is in progress', () => {
    const criterion = aCriterion('a_criterion', CRITERION_IN_PROGRESS);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

    expect(
      getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')
    ).toBeInTheDocument();
    expect(
      getByText('akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress')
    ).toBeInTheDocument();
  });

  test('it displays appropriate message when status is not applicable', () => {
    const criterion = aCriterion('a_criterion', CRITERION_NOT_APPLICABLE);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

    expect(
      getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')
    ).toBeInTheDocument();
    expect(getByText('N/A')).toBeInTheDocument();
  });

  test('it displays success message when there is no attribute to improve', () => {
    const criterionRate = aRate(100, 'A');
    const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

    expect(
      getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')
    ).toBeInTheDocument();
    expect(getByText('akeneo_data_quality_insights.product_evaluation.messages.success.criterion')).toBeInTheDocument();
  });

  test('it displays recommendation message when there are attributes to improve', () => {
    const criterionRate = aRate(85, 'B');
    const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate, ['an_attribute']);
    const rate = aRate();
    const evaluation = anEvaluation(rate, [criterion]);

    const {getByText} = renderCriterion(criterion, 'an_axis', evaluation);

    expect(
      getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')
    ).toBeInTheDocument();
    expect(getByText('an_attribute')).toBeInTheDocument();
  });
});

describe('Criterion for product model', () => {
    test('it displays recommendation message when there are attributes to improve', () => {
        const criterionRate = aRate(85, 'B');
        const criterion = aCriterion('a_criterion', CRITERION_DONE, criterionRate, ['an_attribute']);
        const rate = aRate();
        const evaluation = anEvaluation(rate, [criterion]);
        const productModel = aProductModel();

        const {getByText} = renderCriterion(criterion, 'an_axis', evaluation, {
          product: productModel
        });

        expect(getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')).toBeInTheDocument();
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

        const {getByText} = renderCriterion(criterion, 'an_axis', evaluation, {
          product: variantProduct
        });

        expect(getByText('akeneo_data_quality_insights.product_evaluation.criteria.a_criterion.recommendation' + ':')).toBeInTheDocument();
        expect(getByText('an_attribute')).toBeInTheDocument();
    });
});
