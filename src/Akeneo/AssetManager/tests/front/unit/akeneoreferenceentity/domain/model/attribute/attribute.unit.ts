import {denormalize as denormalizeTextAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/text';
import {denormalize as denormalizeImageAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/image';
import {ConcreteImageAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/image';
import {createIdentifier as denormalizeAttributeIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';

const description = denormalizeTextAttribute({
  identifier: 'description_1234',
  reference_entity_identifier: 'designer',
  code: 'description',
  labels: {en_US: 'Description'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'email',
  regular_expression: null,
});
const frontView = denormalizeImageAttribute({
  identifier: 'front_view_1234',
  reference_entity_identifier: 'designer',
  code: 'front_view',
  labels: {en_US: 'Front view'},
  type: 'image',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_file_size: null,
  allowed_extensions: [],
});

describe('akeneo > attribute > domain > model --- attribute', () => {
  test('I can create a new attribute with a identifier and labels', () => {
    expect(description.getIdentifier()).toEqual(denormalizeAttributeIdentifier('description_1234'));
  });

  test('I can compare two attributes', () => {
    expect(description.equals(frontView)).toEqual(false);
    expect(description.equals(description)).toEqual(true);
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      new ConcreteImageAttribute(
        denormalizeAttributeIdentifier('front_view_1234'),
        createReferenceEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0
      );
    }).toThrow('Attribute expects a boolean as isRequired value');

    expect(() => {
      new ConcreteImageAttribute(
        denormalizeAttributeIdentifier('front_view_1234'),
        createReferenceEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false
      );
    }).toThrow('Attribute expects a number as order');

    expect(() => {
      new ConcreteImageAttribute(
        'front_view_1234',
        createReferenceEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0
      );
    }).toThrow('Attribute expects an AttributeIdentifier argument');
  });
});
