import {ConcreteOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {createOptionFromNormalized} from 'akeneoassetmanager/domain/model/attribute/type/option/option';

const normalizedFavoriteColor = {
  identifier: 'colors',
  asset_family_identifier: 'designer',
  code: 'colors',
  labels: {en_US: 'Colors'},
  type: 'option_collection',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  options: [
    {
      code: 'red',
      labels: {en_US: 'Red'},
    },
    {
      code: 'green',
      labels: {en_US: 'Green'},
    },
  ],
};

describe('akeneo > attribute > domain > model > attribute > type --- OptionCollectionAttribute', () => {
  test('I can create a ConcreteOptionCollectionAttribute from normalized', () => {
    expect(ConcreteOptionCollectionAttribute.createFromNormalized(normalizedFavoriteColor).normalize()).toEqual(
      normalizedFavoriteColor
    );
  });

  test('I can set options', () => {
    const newOption = createOptionFromNormalized({code: 'new_option', labels: {}});
    const optionAttribute = ConcreteOptionCollectionAttribute.createFromNormalized(normalizedFavoriteColor).setOptions([
      newOption,
    ]);
    expect(optionAttribute.normalize()).toEqual({
      ...normalizedFavoriteColor,
      options: [{code: 'new_option', labels: {}}],
    });
  });

  test('I get the options', () => {
    const options = ConcreteOptionCollectionAttribute.createFromNormalized(normalizedFavoriteColor).getOptions();
    expect(options[0]).toEqual({
      code: 'red',
      labels: {en_US: 'Red'},
    });
    expect(options[1]).toEqual({
      code: 'green',
      labels: {en_US: 'Green'},
    });
  });
});
