import {ConcreteNumberAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/number';
import Identifier, {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import ReferenceEntityIdentifier, {
  createIdentifier as createReferenceEntityIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';

const normalizedArea = {
  identifier: 'area_city_fingerprint',
  reference_entity_identifier: 'city',
  code: 'area',
  labels: {en_US: 'Area'},
  type: 'number',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  is_decimal: false,
};

describe('akeneo > attribute > domain > model > attribute > type --- NumberAttribute', () => {
  test('I can create a ConcreteNumberAttribute from normalized', () => {
    expect(ConcreteNumberAttribute.createFromNormalized(normalizedArea).normalize()).toEqual(normalizedArea);
  });

  test('I cannot create an invalid ConcreteNumberAttribute', () => {
    expect(() => {
      new ConcreteNumberAttribute(
        createIdentifier('designer', 'age'),
        createReferenceEntityIdentifier('designer'),
        createCode('age'),
        createLabelCollection({en_US: 'Age'}),
        false,
        false,
        0,
        true,
        false
      );
    }).toThrow('Attribute expects a IsDecimal as isDecimal');
  });
});
