import {
  denormalizeMinimalAttribute,
  MinimalConcreteAttribute,
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

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      new MinimalConcreteAttribute(
        createReferenceEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        'text',
        true
      );
    }).toThrow('Attribute expect a boolean as valuePerChannel');

    expect(() => {
      new MinimalConcreteAttribute(
        createReferenceEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        'text'
      );
    }).toThrow('Attribute expect a boolean as valuePerLocale');
    expect(() => {
      new MinimalConcreteAttribute(
        createReferenceEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'})
      );
    }).toThrow('Attribute expect valid attribute type (text, image)');
    expect(() => {
      new MinimalConcreteAttribute(createReferenceEntityIdentifier('designer'), createCode('description'));
    }).toThrow('Attribute expect a LabelCollection argument');
    expect(() => {
      new MinimalConcreteAttribute(createReferenceEntityIdentifier('designer'));
    }).toThrow('Attribute expect a AttributeCode argument');
    expect(() => {
      new MinimalConcreteAttribute();
    }).toThrow('Attribute expect an ReferenceEntityIdentifier argument');
  });
});
