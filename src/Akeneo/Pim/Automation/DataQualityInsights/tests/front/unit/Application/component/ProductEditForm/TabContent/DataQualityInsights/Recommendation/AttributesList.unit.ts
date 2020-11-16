import {renderAttributesList} from '../../../../../../../utils/render';
import {anEvaluation, aProduct} from '../../../../../../../utils/provider';
import AttributeWithRecommendation from '@akeneo-pim-community/data-quality-insights/src/domain/AttributeWithRecommendation.interface';

describe('AttributesList', () => {
  test('it displays a success message when there is no attributes to improve', () => {
    const product = aProduct();
    const evaluation = anEvaluation();
    const attributes: AttributeWithRecommendation[] = [];

    const {getByText} = renderAttributesList('a_criterion', 'an_axis', attributes, evaluation, {
      product,
    });

    expect(getByText('akeneo_data_quality_insights.product_evaluation.messages.success.criterion')).toBeInTheDocument();
  });

  test('it displays the list of attributes to improve', () => {
    const product = aProduct();
    const evaluation = anEvaluation();
    const attributes: AttributeWithRecommendation[] = [{code: 'an_attribute', label: 'an_attribute'}];

    const {getByText} = renderAttributesList('a_criterion', 'an_axis', attributes, evaluation, {
      product,
    });

    expect(getByText('an_attribute')).toBeInTheDocument();
  });

  test('it displays a message with too many attributes to improve when the list exceeds 15 items', () => {
    const product = aProduct();
    const evaluation = anEvaluation();
    const attributes: AttributeWithRecommendation[] = [
      {code: 'attribute1', label: 'attribute1'},
      {code: 'attribute2', label: 'attribute2'},
      {code: 'attribute3', label: 'attribute3'},
      {code: 'attribute4', label: 'attribute4'},
      {code: 'attribute5', label: 'attribute5'},
      {code: 'attribute6', label: 'attribute6'},
      {code: 'attribute7', label: 'attribute7'},
      {code: 'attribute8', label: 'attribute8'},
      {code: 'attribute9', label: 'attribute9'},
      {code: 'attribute10', label: 'attribute10'},
      {code: 'attribute11', label: 'attribute11'},
      {code: 'attribute12', label: 'attribute12'},
      {code: 'attribute13', label: 'attribute13'},
      {code: 'attribute14', label: 'attribute14'},
      {code: 'attribute15', label: 'attribute15'},
      {code: 'attribute16', label: 'attribute16'},
    ];

    const {getByText} = renderAttributesList('a_criterion', 'an_axis', attributes, evaluation, {
      product,
    });

    expect(
      getByText('akeneo_data_quality_insights.product_evaluation.messages.too_many_attributes')
    ).toBeInTheDocument();
  });
});
