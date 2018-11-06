import {ConcreteOptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {createIdentifier as createReferenceEntityIdentifier,} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {AttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';

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
      labelCollection: {en_US: 'Red'},
    },
    {
      code: 'green',
      labelCollection: {en_US: 'Green'},
    }
  ],
};

describe('akeneo > attribute > domain > model > attribute > type --- OptionAttribute', () => {
  test('I can create a ConcreteOptionAttribute from normalized', () => {
    expect(ConcreteOptionAttribute.createFromNormalized(normalizedFavoriteColor).normalize()).toEqual(normalizedFavoriteColor);
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
});
