import {followAttributeSpellingCriterion} from '@akeneo-pim-ee/data-quality-insights/src/application';
import {redirectToAttributeGridFilteredByFamilyAndQuality} from '@akeneo-pim-ee/data-quality-insights/src/infrastructure/navigation/AttributeGridRouter';
import {BACK_LINK_SESSION_STORAGE_KEY} from '@akeneo-pim-community/data-quality-insights/src/application/constant';
import {aCriterion, aFamily, aProduct, aProductModel, aRate} from '../../../utils/provider';

jest.mock('@akeneo-pim-ee/data-quality-insights/src/infrastructure/navigation/AttributeGridRouter');

describe('followAttributeSpellingCriterion', () => {
  beforeEach(() => {
    jest.resetAllMocks();
    sessionStorage.clear();
  });
  afterAll(() => jest.resetAllMocks());

  test('it does not redirects when family is not defined', () => {
    const criterionRate = aRate(85, 'B');
    const criterion = aCriterion('consistency_attribute_spelling', 'done', criterionRate, ['an_attribute']);
    const product = aProduct(1234);
    followAttributeSpellingCriterion(criterion, null, product, 'en_US');

    expect(redirectToAttributeGridFilteredByFamilyAndQuality).not.toHaveBeenCalled();
  });

  test('it does not redirects when evaluation has succeed', () => {
    const criterionRate = aRate(100, 'A');
    const criterion = aCriterion('consistency_attribute_spelling', 'done', criterionRate, ['an_attribute']);
    const product = aProduct(1234);
    const family = aFamily('a_family', 4321);

    followAttributeSpellingCriterion(criterion, family, product, 'en_US');

    expect(redirectToAttributeGridFilteredByFamilyAndQuality).not.toHaveBeenCalled();
  });

  test('it initializes back link data with product information before redirecting to the attribute grid', () => {
    const criterionRate = aRate(85, 'B');
    const criterion = aCriterion('consistency_attribute_spelling', 'done', criterionRate, ['an_attribute']);
    const product = aProduct(1234);
    const family = aFamily('a_family', 4321);

    followAttributeSpellingCriterion(criterion, family, product, 'en_US');

    const backLink = JSON.parse(sessionStorage.getItem(BACK_LINK_SESSION_STORAGE_KEY) as string);
    expect(backLink.route).toBe('pim_enrich_product_edit');
    expect(backLink.routeParams.id).toBe(1234);

    expect(redirectToAttributeGridFilteredByFamilyAndQuality).toHaveBeenCalledWith('a_family', 'en_US');
  });

  test('it initializes back link data with product model information before redirecting to the attribute grid', () => {
    const criterionRate = aRate(85, 'B');
    const criterion = aCriterion('consistency_attribute_spelling', 'done', criterionRate, ['an_attribute']);
    const product = aProductModel(1234);
    const family = aFamily('a_family', 4321);

    followAttributeSpellingCriterion(criterion, family, product, 'en_US');

    const backLink = JSON.parse(sessionStorage.getItem(BACK_LINK_SESSION_STORAGE_KEY) as string);
    expect(backLink.route).toBe('pim_enrich_product_model_edit');
    expect(backLink.routeParams.id).toBe(1234);

    expect(redirectToAttributeGridFilteredByFamilyAndQuality).toHaveBeenCalledWith('a_family', 'en_US');
  });
});
