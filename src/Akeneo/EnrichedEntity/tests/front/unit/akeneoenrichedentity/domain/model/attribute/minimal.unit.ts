import {
  denormalizeMinimalAttribute,
  MinimalConcreteAttribute,
} from 'akeneoenrichedentity/domain/model/attribute/minimal';
import {createIdentifier as denormalizeAttributeIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';
import {createIdentifier as createEnrichedEntityIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createCode} from 'akeneoenrichedentity/domain/model/attribute/code';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

const description = denormalizeMinimalAttribute({
  identifier: 'description',
  enriched_entity_identifier: 'designer',
  code: 'description',
  type: 'text',
  labels: {en_US: 'Description'},
  value_per_locale: true,
  value_per_channel: false,
});
const frontView = denormalizeMinimalAttribute({
  identifier: 'front_fiew',
  enriched_entity_identifier: 'designer',
  code: 'front_fiew',
  type: 'image',
  labels: {en_US: 'Front View'},
  value_per_locale: false,
  value_per_channel: true,
});

describe('akeneo > attribute > domain > model --- minimal attribute', () => {
  test('I can create a new attribute with a identifier and labels', () => {
    expect(description.getIdentifier()).toEqual(denormalizeAttributeIdentifier('description'));
    expect(description.getEnrichedEntityIdentifier()).toEqual(createEnrichedEntityIdentifier('designer'));
    expect(description.getCode()).toEqual(createCode('description'));
    expect(description.getType()).toEqual('text');
    expect(description.getLabel('en_US')).toEqual('Description');
    expect(description.getLabel('fr_fr')).toEqual('[description]');
    expect(description.getLabel('fr_fr', false)).toEqual('');
    expect(description.equals(description)).toEqual(true);
    expect(description.equals(frontView)).toEqual(false);
    expect(description.getLabelCollection()).toEqual(createLabelCollection({en_US: 'Description'}));
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      new MinimalConcreteAttribute(
        denormalizeAttributeIdentifier('front_view'),
        createEnrichedEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        'text',
        true
      );
    }).toThrow('Attribute expect a boolean as valuePerChannel');

    expect(() => {
      new MinimalConcreteAttribute(
        denormalizeAttributeIdentifier('front_view'),
        createEnrichedEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        'text'
      );
    }).toThrow('Attribute expect a boolean as valuePerLocale');
    expect(() => {
      new MinimalConcreteAttribute(
        denormalizeAttributeIdentifier('front_view'),
        createEnrichedEntityIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'})
      );
    }).toThrow('Attribute expect valid attribute type (text, image)');
    expect(() => {
      new MinimalConcreteAttribute(
        denormalizeAttributeIdentifier('front_view'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description')
      );
    }).toThrow('Attribute expect a LabelCollection argument');
    expect(() => {
      new MinimalConcreteAttribute(
        denormalizeAttributeIdentifier('front_view'),
        createEnrichedEntityIdentifier('designer')
      );
    }).toThrow('Attribute expect a AttributeCode argument');
    expect(() => {
      new MinimalConcreteAttribute(denormalizeAttributeIdentifier('front_view'));
    }).toThrow('Attribute expect an EnrichedEntityIdentifier argument');
    expect(() => {
      new MinimalConcreteAttribute();
    }).toThrow('Attribute expect an AttributeIdentifier argument');
  });
});
