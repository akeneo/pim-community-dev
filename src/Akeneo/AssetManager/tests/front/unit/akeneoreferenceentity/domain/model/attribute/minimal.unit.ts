import {
  denormalizeMinimalAttribute,
  MinimalConcreteAttribute,
  MinimalRecordConcreteAttribute,
} from 'akeneoreferenceentity/domain/model/attribute/minimal';
import {createIdentifier as denormalizeAttributeIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';

const description = denormalizeMinimalAttribute({
  reference_entity_identifier: 'designer',
  code: 'description',
  type: 'text',
  labels: {en_US: 'Description'},
  value_per_locale: true,
  value_per_channel: false,
});
const brands = denormalizeMinimalAttribute({
  reference_entity_identifier: 'designer',
  code: 'brands',
  type: 'record',
  record_type: 'brands',
  labels: {en_US: 'Brands'},
  value_per_locale: true,
  value_per_channel: false,
});
const frontView = denormalizeMinimalAttribute({
  reference_entity_identifier: 'designer',
  code: 'front_fiew',
  type: 'image',
  labels: {en_US: 'Front View'},
  value_per_locale: false,
  value_per_channel: true,
});

describe('akeneo > attribute > domain > model --- minimal attribute', () => {
  test('I can create a new attribute with a identifier and labels', () => {
    expect(description.getReferenceEntityIdentifier()).toEqual(createReferenceEntityIdentifier('designer'));
    expect(description.getCode()).toEqual(createCode('description'));
    expect(description.getType()).toEqual('text');
    expect(description.getLabel('en_US')).toEqual('Description');
    expect(description.getLabel('fr_fr')).toEqual('[description]');
    expect(description.getLabel('fr_fr', false)).toEqual('');
    expect(description.getLabelCollection()).toEqual(createLabelCollection({en_US: 'Description'}));
  });
  test('I can create a new record attribute with a identifier and labels', () => {
    expect(brands.getReferenceEntityIdentifier()).toEqual(createReferenceEntityIdentifier('designer'));
    expect(brands.getCode()).toEqual(createCode('brands'));
    expect(brands.getType()).toEqual('record');
    expect(brands.getLabel('en_US')).toEqual('Brands');
    expect(brands.getLabel('fr_fr')).toEqual('[brands]');
    expect(brands.getLabel('fr_fr', false)).toEqual('');
    expect(brands.getLabelCollection()).toEqual(createLabelCollection({en_US: 'Brands'}));
  });

  test('I can normalize a record attribute', () => {
    expect(brands.normalize()).toEqual({
      code: 'brands',
      labels: {en_US: 'Brands'},
      record_type: 'brands',
      reference_entity_identifier: 'designer',
      type: 'record',
      value_per_channel: false,
      value_per_locale: true,
    });
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      new MinimalConcreteAttribute(
        createReferenceEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        'text',
        true
      );
    }).toThrow('Attribute expects a boolean as valuePerChannel');

    expect(() => {
      new MinimalRecordConcreteAttribute(
        createReferenceEntityIdentifier('designer'),
        createCode('brands'),
        createLabelCollection({en_US: 'Brands'}),
        'record',
        true,
        false
      );
    }).toThrow('Attribute expects a RecordType argument');

    expect(() => {
      new MinimalRecordConcreteAttribute(
        createReferenceEntityIdentifier('designer'),
        createCode('brands'),
        createLabelCollection({en_US: 'Brands'}),
        'text',
        true,
        false
      );
    }).toThrow('MinimalRecordAttribute type needs to be "record" or "record_collection"');

    expect(() => {
      new MinimalConcreteAttribute(
        createReferenceEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        'text'
      );
    }).toThrow('Attribute expects a boolean as valuePerLocale');
    expect(() => {
      new MinimalConcreteAttribute(
        createReferenceEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'})
      );
    }).toThrow('Attribute expects a string as attribute type');
    expect(() => {
      new MinimalConcreteAttribute(createReferenceEntityIdentifier('designer'), createCode('description'));
    }).toThrow('Attribute expects a LabelCollection argument');
    expect(() => {
      new MinimalConcreteAttribute(createReferenceEntityIdentifier('designer'));
    }).toThrow('Attribute expects a AttributeCode argument');
    expect(() => {
      new MinimalConcreteAttribute();
    }).toThrow('Attribute expects an ReferenceEntityIdentifier argument');
  });
});
