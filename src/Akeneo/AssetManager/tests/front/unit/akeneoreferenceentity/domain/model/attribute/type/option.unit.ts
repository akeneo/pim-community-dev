import {ConcreteOptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';

const normalizedFavoriteColor = {
  identifier: 'favorite_color',
  reference_entity_identifier: 'designer',
  code: 'favorite_color',
  labels: {en_US: 'Favorite color'},
  type: 'option',
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

describe('akeneo > attribute > domain > model > attribute > type --- OptionAttribute', () => {
  test('I can create a ConcreteOptionAttribute from normalized', () => {
    expect(ConcreteOptionAttribute.createFromNormalized(normalizedFavoriteColor).normalize()).toEqual(
      normalizedFavoriteColor
    );
  });

  test('I cannot create an invalid ConcreteOptionAttribute', () => {
    expect(() => {
      new ConcreteOptionAttribute(
        createIdentifier('designer', 'favorite_color'),
        createReferenceEntityIdentifier('designer'),
        createCode('favorite_color'),
        createLabelCollection({en_US: 'Favorite color'}),
        true,
        false,
        0,
        true,
        ['hello']
      );
    }).toThrow('Attribute expects a list of Option as options');
  });

  test('I can set options', () => {
    const newOption = Option.createFromNormalized({code: 'new_option', labels: {}});
    const optionAttribute = ConcreteOptionAttribute.createFromNormalized(normalizedFavoriteColor).setOptions([
      newOption,
    ]);
    expect(optionAttribute.normalize()).toEqual({
      ...normalizedFavoriteColor,
      options: [{code: 'new_option', labels: {}}],
    });
  });

  test('I get the options', () => {
    const options = ConcreteOptionAttribute.createFromNormalized(normalizedFavoriteColor).getOptions();
    expect(options[0].normalize()).toEqual({
      code: 'red',
      labels: {en_US: 'Red'},
    });
    expect(options[1].normalize()).toEqual({
      code: 'green',
      labels: {en_US: 'Green'},
    });
  });
});
