import {fireEvent} from '@testing-library/react';
import {
    aCriterion, aFamily,
    anEvaluation, aProduct,
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
import {
    redirectToAttributeGridFilteredByFamilyAndQuality,
    redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes
} from "@akeneo-pim-community/data-quality-insights/src/infrastructure/AttributeGridRouter";
import {
    ATTRIBUTE_OPTION_SPELLING_CRITERION_CODE,
    ATTRIBUTE_SPELLING_CRITERION_CODE,
    BACK_LINK_SESSION_STORAGE_KEY
} from "@akeneo-pim-community/data-quality-insights/src/application/constant";

jest.mock("@akeneo-pim-community/data-quality-insights/src/infrastructure/AttributeGridRouter");

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

  test('it displays proper recommendation when product do not have image attribute', () => {
      const criterionRate = aRate(0, 'E');
      const criterion = aCriterion('missing_image_attribute', CRITERION_DONE, criterionRate, []);
      const rate = aRate();
      const evaluation = anEvaluation(rate, [criterion]);

      const {getByText} = renderCriterion(criterion, 'enrichment', evaluation);

      expect(
          getByText('akeneo_data_quality_insights.product_evaluation.criteria.missing_image_attribute.recommendation' + ':')
      ).toBeInTheDocument();
      expect(getByText('akeneo_data_quality_insights.product_evaluation.messages.add_image_attribute_recommendation')).toBeInTheDocument();
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

describe('Criterion user actions', () => {
    beforeEach(() => {
        jest.resetAllMocks();
        sessionStorage.clear();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    test('it redirects to the attribute grid filtered by family, quality and selected attributes types when criterion is attribute spelling', () => {
        const criterionRate = aRate(85, 'B');
        const criterion = aCriterion(ATTRIBUTE_SPELLING_CRITERION_CODE, CRITERION_DONE, criterionRate, ['an_attribute']);
        const rate = aRate();
        const evaluation = anEvaluation(rate, [criterion]);
        const product = aProduct(1234);
        const family = aFamily('a_family', 4321);

        const {getByText} = renderCriterion(criterion, 'an_axis', evaluation, {
            families: {
                a_family: family
            },
            product
        });

        fireEvent.click(getByText('an_attribute'));

        const backLink = JSON.parse(sessionStorage.getItem(BACK_LINK_SESSION_STORAGE_KEY) as string);
        expect(backLink.route).toBe('pim_enrich_product_edit');
        expect(backLink.routeParams.id).toBe(1234);
        expect(redirectToAttributeGridFilteredByFamilyAndQuality).toHaveBeenCalledWith(4321);
    });
    test('it redirects to the attribute grid filtered by family, quality and selected attributes types when criterion is attribute option spelling', () => {

        const criterionRate = aRate(85, 'B');
        const criterion = aCriterion(ATTRIBUTE_OPTION_SPELLING_CRITERION_CODE, CRITERION_DONE, criterionRate, ['an_attribute']);
        const rate = aRate();
        const evaluation = anEvaluation(rate, [criterion]);
        const product = aProduct(1234);
        const family = aFamily('a_family', 4321);

        const {getByText} = renderCriterion(criterion, 'an_axis', evaluation, {
            families: {
                a_family: family
            },
            product
        });

        fireEvent.click(getByText('an_attribute'));

        const backLink = JSON.parse(sessionStorage.getItem(BACK_LINK_SESSION_STORAGE_KEY) as string);
        expect(backLink.route).toBe('pim_enrich_product_edit');
        expect(backLink.routeParams.id).toBe(1234);
        expect(redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes).toHaveBeenCalledWith(4321);
    });
});
