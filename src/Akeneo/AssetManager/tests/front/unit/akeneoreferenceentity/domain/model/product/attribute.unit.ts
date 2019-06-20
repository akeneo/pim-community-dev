import {createCode as denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/product/attribute/code';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {createAttribute, denormalizeAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {denormalizeFile} from 'akeneoassetmanager/domain/model/file';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

const frontView = denormalizeAttribute({
  code: 'front_view',
  type: 'akeneo_asset',
  labels: {en_US: 'My nice attribute'},
  reference_data_name: 'brand',
  useable_as_grid_filter: true,
});
const sideView = denormalizeAttribute({
  code: 'side_view',
  type: 'akeneo_asset',
  labels: {en_US: 'My nice attribute'},
  reference_data_name: 'brand',
  useable_as_grid_filter: true,
});

describe('akeneo > asset family > domain > model --- attribute', () => {
  test('I can create a new attribute', () => {
    expect(frontView.getCode()).toEqual(denormalizeAttributeCode('front_view'));
  });

  test('I can compare two attributes', () => {
    expect(frontView.equals(frontView)).toEqual(true);
    expect(frontView.equals(sideView)).toEqual(false);
  });

  test('I can get the type of an attribute', () => {
    expect(frontView.getType()).toEqual('akeneo_asset');
  });

  test('I can get the label of an attribute', () => {
    expect(frontView.getLabel('en_US')).toEqual('My nice attribute');
    expect(frontView.getLabel('fr_FR')).toEqual('[front_view]');
    expect(frontView.getLabel('fr_FR', false)).toEqual('');
  });

  test('I can get the label collection of an attribute', () => {
    expect(frontView.getLabelCollection()).toEqual(createLabelCollection({en_US: 'My nice attribute'}));
  });

  test('I can get know if the attribute is useable in the grid', () => {
    expect(frontView.getUseableAsGridFilter()).toEqual(true);
  });

  test('I can normalize my attribute', () => {
    expect(frontView.normalize()).toEqual({
      code: 'front_view',
      type: 'akeneo_asset',
      labels: {en_US: 'My nice attribute'},
      reference_data_name: 'brand',
      useable_as_grid_filter: true,
    });
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      createAttribute(
        'nice_attribute',
        'akeneo_asset',
        createLabelCollection({en_US: 'My nice attribute'}),
        'brand',
        false
      );
    }).toThrow('Attribute expects an AttributeCode as code argument');

    expect(() => {
      createAttribute(
        denormalizeAttributeCode('nice_attribute'),
        12,
        createLabelCollection({en_US: 'My nice attribute'}),
        'brand',
        false
      );
    }).toThrow('Attribute expects a string as type argument');

    expect(() => {
      createAttribute(
        denormalizeAttributeCode('nice_attribute'),
        'akeneo_asset',
        {en_US: 'My nice attribute'},
        'brand',
        false
      );
    }).toThrow('Attribute expects a LabelCollection as labelCollection argument');

    expect(() => {
      createAttribute(
        denormalizeAttributeCode('nice_attribute'),
        'akeneo_asset',
        createLabelCollection({en_US: 'My nice attribute'}),
        null,
        false
      );
    }).toThrow('Attribute expects a string as referenceDataName argument');

    expect(() => {
      createAttribute(
        denormalizeAttributeCode('nice_attribute'),
        'akeneo_asset',
        createLabelCollection({en_US: 'My nice attribute'}),
        'brand',
        'false'
      );
    }).toThrow('Attribute expects a boolean as type argument');
  });
});
