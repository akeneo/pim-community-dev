import {renderAttributesListWithVariations} from '../../../../../../utils/render';
import {anEvaluation, aProductModel, aVariantProduct} from '../../../../../../utils/provider';
import AttributeWithRecommendation from '@akeneo-pim-community/data-quality-insights/src/domain/AttributeWithRecommendation.interface';

describe('AttributesListWithVariations', () => {
  test('it displays list of attributes for Root product model', () => {
    const rootProductModel = aProductModel();
    const attributes: AttributeWithRecommendation[] = [{code: 'an_attribute', label: 'An attribute'}];
    const evaluation = anEvaluation();

    const {getByText} = renderAttributesListWithVariations(
      rootProductModel,
      'a_criterion',
      'en_US',
      'an_axis',
      attributes,
      evaluation
    );

    expect(getByText('An attribute')).toBeInTheDocument();
  });

  test('it displays list of attributes for sub product model', () => {
    const subProductModel = aProductModel(1234, 1);
    const attributes: AttributeWithRecommendation[] = [{code: 'an_attribute', label: 'An attribute'}];
    const evaluation = anEvaluation();

    const {getByText, getByTestId} = renderAttributesListWithVariations(
      subProductModel,
      'a_criterion',
      'en_US',
      'an_axis',
      attributes,
      evaluation
    );

    expect(getByTestId('attributes-level-0')).toBeInTheDocument();
    expect(getByTestId('attributes-level-1')).toBeInTheDocument();
    expect(getByText('An attribute')).toBeInTheDocument();
  });

  test('it displays list of attributes for product variant', () => {
    const variantProduct = aVariantProduct();
    const attributes: AttributeWithRecommendation[] = [{code: 'an_attribute', label: 'An attribute'}];
    const evaluation = anEvaluation();

    const {getByText, getByTestId} = renderAttributesListWithVariations(
      variantProduct,
      'a_criterion',
      'en_US',
      'an_axis',
      attributes,
      evaluation
    );

    expect(getByTestId('attributes-level-0')).toBeInTheDocument();
    expect(getByTestId('attributes-level-1')).toBeInTheDocument();
    expect(getByText('An attribute')).toBeInTheDocument();
  });

  test('it displays list of attributes for product variant with multiple levels of variations', () => {
    const variantProduct = aVariantProduct(
      1234,
      {},
      2,
      'idx_1234',
      'a_family',
      ['an_attribute', 'a_second_attribute', 'a_level2_axis_attribute'],
      [
        {axes: {en_US: 'Level 0 Model'}, selected: {id: 1}},
        {axes: {en_US: 'Level 1 Model'}, selected: {id: 12}},
        {axes: {en_US: 'Level 2 Model'}, selected: {id: 123}},
      ],
      [{attributes: ['a_root_attribute']}, {attributes: ['an_level1_attribute']}, {attributes: ['an_level2_attribute']}]
    );
    const attributes: AttributeWithRecommendation[] = [{code: 'an_attribute', label: 'An attribute'}];
    const evaluation = anEvaluation();

    const {getByText, getByTestId} = renderAttributesListWithVariations(
      variantProduct,
      'a_criterion',
      'en_US',
      'an_axis',
      attributes,
      evaluation
    );

    expect(getByTestId('attributes-level-0')).toBeInTheDocument();
    expect(getByTestId('attributes-level-1')).toBeInTheDocument();
    expect(getByText('An attribute')).toBeInTheDocument();
  });
});
