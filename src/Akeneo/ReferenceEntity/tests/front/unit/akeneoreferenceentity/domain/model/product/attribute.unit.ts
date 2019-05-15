import {createCode as denormalizeAttributeCode} from 'akeneoreferenceentity/domain/model/product/attribute/code';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createAttribute, denormalizeAttribute} from 'akeneoreferenceentity/domain/model/product/attribute';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';

const frontView = denormalizeAttribute({
  code: 'front_view',
  type: 'akeneo_reference_entity',
  labels: {en_US: 'My nice attribute'},
  reference_data_name: 'brand',
});
const sideView = denormalizeAttribute({
  code: 'side_view',
  type: 'akeneo_reference_entity',
  labels: {en_US: 'My nice attribute'},
  reference_data_name: 'brand',
});

describe('akeneo > reference entity > domain > model --- attribute', () => {
  test('I can create a new attribute', () => {
    expect(frontView.getCode()).toEqual(denormalizeAttributeCode('front_view'));
  });

  test('I can compare two attributes', () => {
    expect(frontView.equals(frontView)).toEqual(true);
    expect(frontView.equals(sideView)).toEqual(false);
  });

  test('I can get the type of an attribute', () => {
    expect(frontView.getType()).toEqual('akeneo_reference_entity');
  });

  test('I can get the label of an attribute', () => {
    expect(frontView.getLabel('en_US')).toEqual('My nice attribute');
    expect(frontView.getLabel('fr_FR')).toEqual('[front_view]');
    expect(frontView.getLabel('fr_FR', false)).toEqual('');
  });

  test('I can get the label collection of an attribute', () => {
    expect(frontView.getLabelCollection()).toEqual(createLabelCollection({en_US: 'My nice attribute'}));
  });

  test('I can normalize my attribute', () => {
    expect(frontView.normalize()).toEqual({
      code: 'front_view',
      type: 'akeneo_reference_entity',
      labels: {en_US: 'My nice attribute'},
      reference_data_name: 'brand',
    });
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      createAttribute(
        'nice_attribute',
        'akeneo_reference_entity',
        createLabelCollection({en_US: 'My nice attribute'}),
        'brand'
      );
    }).toThrow('Attribute expects an AttributeCode as code argument');

    expect(() => {
      createAttribute(
        denormalizeAttributeCode('nice_attribute'),
        12,
        createLabelCollection({en_US: 'My nice attribute'}),
        'brand'
      );
    }).toThrow('Attribute expects a string as type argument');

    expect(() => {
      createAttribute(
        denormalizeAttributeCode('nice_attribute'),
        'akeneo_reference_entity',
        {en_US: 'My nice attribute'},
        'brand'
      );
    }).toThrow('Attribute expects a LabelCollection as labelCollection argument');

    expect(() => {
      createAttribute(
        denormalizeAttributeCode('nice_attribute'),
        'akeneo_reference_entity',
        createLabelCollection({en_US: 'My nice attribute'}),
        null
      );
    }).toThrow('Attribute expects a string as referenceDataName argument');
  });
});
