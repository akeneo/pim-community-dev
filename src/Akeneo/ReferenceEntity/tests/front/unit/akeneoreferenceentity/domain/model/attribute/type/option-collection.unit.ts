import {ConcreteOptionCollectionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option-collection';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {AttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';

const normalizedFavoriteColor = {
  identifier: 'colors',
  reference_entity_identifier: 'designer',
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
  test('I cannot create an invalid ConcreteOptionCollectionAttribute', () => {
    expect(() => {
      new ConcreteOptionCollectionAttribute(
        createIdentifier('designer', 'colors'),
        createReferenceEntityIdentifier('designer'),
        createCode('colors'),
        createLabelCollection({en_US: 'Colors'}),
        true,
        false,
        0,
        true,
        ['hello']
      );
    }).toThrow('Attribute expects a list of Option as options');
  });
});
