import {
  denormalizeAttribute,
  AttributeType,
  ConcreteTextAttribute,
  ConcreteImageAttribute,
} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {createIdentifier as denormalizeAttributeIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';
import {createIdentifier as createEnrichedEntityIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createCode} from 'akeneoenrichedentity/domain/model/attribute/code';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

const description = denormalizeAttribute({
  identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
  enrichedEntityIdentifier: 'designer',
  code: 'description',
  labels: {en_US: 'Description'},
  type: 'text',
  order: 0,
  valuePerLocale: true,
  valuePerChannel: false,
  required: true,
});
const fronView = denormalizeAttribute({
  identifier: {identifier: 'front_view', enrichedEntityIdentifier: 'designer'},
  enrichedEntityIdentifier: 'designer',
  code: 'front_view',
  labels: {en_US: 'Front view'},
  type: 'image',
  order: 0,
  valuePerLocale: true,
  valuePerChannel: false,
  required: true,
});

describe('akeneo > attribute > domain > model --- attribute', () => {
  test('I can create a new attribute with a identifier and labels', () => {
    expect(description.getIdentifier()).toEqual(denormalizeAttributeIdentifier('designer', 'description'));
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      denormalizeAttribute({
        identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
        enrichedEntityIdentifier: 'designer',
        type: 'text',
      });
    }).toThrow('Code expect a string as parameter to be created');
    expect(() => {
      denormalizeAttribute({
        identifier: {identifier: 12, enrichedEntityIdentifier: 'designer'},
        type: 'text',
      });
    }).toThrow('AttributeIdentifier expect a string as second parameter to be created');
    expect(() => {
      denormalizeAttribute({
        identifier: {identifier: 'name', enrichedEntityIdentifier: 12},
        type: 'text',
      });
    }).toThrow('AttributeIdentifier expect a string as first parameter to be created');
    expect(() => {
      denormalizeAttribute({
        identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
        type: 'text',
      });
    }).toThrow('Identifier expect a string as parameter to be created');
    expect(() => {
      denormalizeAttribute({
        identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
        enrichedEntityIdentifier: 'designer',
        code: 'description',
        type: 'text',
      });
    }).toThrow('LabelCollection expect only values as {"en_US": "My label"} to be created');
    expect(() => {
      denormalizeAttribute({
        identifier: {identifier: 'name', enrichedEntityIdentifier: 'designer'},
        enrichedEntityIdentifier: 'designer',
        labels: {en_US: 'My label'},
        code: 'description',
        type: 'text',
      });
    }).toThrow('Attribute expect an identifier complient to the given enrichedEntityIdentifier and code');
    expect(() => {
      denormalizeAttribute({
        identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
        enrichedEntityIdentifier: 'designer',
        labels: {en_US: 'My label'},
        code: 'description',
        type: 'awesome',
      });
    }).toThrow('Attribute type "awesome" is not supported');
  });

  test('I can compare two attribute', () => {
    const description = denormalizeAttribute({
      identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
      enrichedEntityIdentifier: 'designer',
      labels: {en_US: 'My label'},
      code: 'description',
      type: 'text',
    });
    const anotherDescription = denormalizeAttribute({
      identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
      enrichedEntityIdentifier: 'designer',
      labels: {en_US: 'My label'},
      code: 'description',
      type: 'image',
    });
    const name = denormalizeAttribute({
      identifier: {identifier: 'description', enrichedEntityIdentifier: 'name'},
      enrichedEntityIdentifier: 'name',
      labels: {en_US: 'My label'},
      code: 'description',
      type: 'image',
    });

    expect(description.equals(description)).toBe(true);
    expect(description.equals(anotherDescription)).toBe(true);
    expect(description.equals(name)).toBe(false);
  });

  test('I can cannot create a malformed text attribute', () => {
    expect(() => {
      new ConcreteTextAttribute();
    }).toThrow('Attribute expect a AttributeIdentifier as first argument');

    expect(() => {
      new ConcreteTextAttribute(denormalizeAttributeIdentifier('designer', 'description'));
    }).toThrow('Attribute expect an EnrichedEntityIdentifier as second argument');

    expect(() => {
      new ConcreteTextAttribute(
        denormalizeAttributeIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer')
      );
    }).toThrow('Attribute expect a AttributeCode as third argument');

    expect(() => {
      new ConcreteTextAttribute(
        denormalizeAttributeIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description')
      );
    }).toThrow('Attribute expect a LabelCollection as fourth argument');

    expect(() => {
      new ConcreteTextAttribute(
        denormalizeAttributeIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('name'),
        createLabelCollection({en_US: 'Description'})
      );
    }).toThrow('Attribute expect an identifier complient to the given enrichedEntityIdentifier and code');
  });

  test('I can cannot create a malformed image attribute', () => {
    expect(() => {
      new ConcreteImageAttribute();
    }).toThrow('Attribute expect a AttributeIdentifier as first argument');

    expect(() => {
      new ConcreteImageAttribute(denormalizeAttributeIdentifier('designer', 'description'));
    }).toThrow('Attribute expect an EnrichedEntityIdentifier as second argument');

    expect(() => {
      new ConcreteImageAttribute(
        denormalizeAttributeIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer')
      );
    }).toThrow('Attribute expect a AttributeCode as third argument');

    expect(() => {
      new ConcreteImageAttribute(
        denormalizeAttributeIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description')
      );
    }).toThrow('Attribute expect a LabelCollection as fourth argument');

    expect(() => {
      new ConcreteImageAttribute(
        denormalizeAttributeIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('name'),
        createLabelCollection({en_US: 'Description'})
      );
    }).toThrow('Attribute expect an identifier complient to the given enrichedEntityIdentifier and code');
  });

  test('I can get the code of the attribute', () => {
    expect(description.getCode().stringValue()).toBe('description');
  });

  test('I can get the enriched entity identifier of the attribute', () => {
    expect(description.getEnrichedEntityIdentifier().stringValue()).toBe('designer');
  });

  test('I can normalize an attribute', () => {
    expect(description.normalize()).toEqual({
      code: 'description',
      enrichedEntityIdentifier: 'designer',
      identifier: {enrichedEntityIdentifier: 'designer', identifier: 'description'},
      labels: {en_US: 'Description'},
      maxLength: undefined,
      order: 0,
      required: true,
      type: 'text',
      valuePerChannel: false,
      valuePerLocale: true,
    });
  });

  test('I can get a label for the given locale', () => {
    expect(description.getLabel('en_US')).toBe('Description');
    expect(description.getLabel('fr_FR')).toBe('[description]');
    expect(description.getLabelCollection()).toEqual({labels: {en_US: 'Description'}});
  });
});
