import {
  denormalizeMinimalAttribute,
  MinimalConcreteAttribute,
  MinimalAssetConcreteAttribute,
} from 'akeneoassetmanager/domain/model/attribute/minimal';
import {createIdentifier as denormalizeAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {createIdentifier as createAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {createCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';

const description = denormalizeMinimalAttribute({
  asset_family_identifier: 'designer',
  code: 'description',
  type: 'text',
  labels: {en_US: 'Description'},
  value_per_locale: true,
  value_per_channel: false,
});
const brands = denormalizeMinimalAttribute({
  asset_family_identifier: 'designer',
  code: 'brands',
  type: 'asset',
  asset_type: 'brands',
  labels: {en_US: 'Brands'},
  value_per_locale: true,
  value_per_channel: false,
});
const frontView = denormalizeMinimalAttribute({
  asset_family_identifier: 'designer',
  code: 'front_fiew',
  type: 'image',
  labels: {en_US: 'Front View'},
  value_per_locale: false,
  value_per_channel: true,
});

describe('akeneo > attribute > domain > model --- minimal attribute', () => {
  test('I can create a new attribute with a identifier and labels', () => {
    expect(description.getAssetFamilyIdentifier()).toEqual(createAssetFamilyIdentifier('designer'));
    expect(description.getCode()).toEqual(createCode('description'));
    expect(description.getType()).toEqual('text');
    expect(description.getLabel('en_US')).toEqual('Description');
    expect(description.getLabel('fr_fr')).toEqual('[description]');
    expect(description.getLabel('fr_fr', false)).toEqual('');
    expect(description.getLabelCollection()).toEqual(createLabelCollection({en_US: 'Description'}));
  });
  test('I can create a new asset attribute with a identifier and labels', () => {
    expect(brands.getAssetFamilyIdentifier()).toEqual(createAssetFamilyIdentifier('designer'));
    expect(brands.getCode()).toEqual(createCode('brands'));
    expect(brands.getType()).toEqual('asset');
    expect(brands.getLabel('en_US')).toEqual('Brands');
    expect(brands.getLabel('fr_fr')).toEqual('[brands]');
    expect(brands.getLabel('fr_fr', false)).toEqual('');
    expect(brands.getLabelCollection()).toEqual(createLabelCollection({en_US: 'Brands'}));
  });

  test('I can normalize a asset attribute', () => {
    expect(brands.normalize()).toEqual({
      code: 'brands',
      labels: {en_US: 'Brands'},
      asset_type: 'brands',
      asset_family_identifier: 'designer',
      type: 'asset',
      value_per_channel: false,
      value_per_locale: true,
    });
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      new MinimalConcreteAttribute(
        createAssetFamilyIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        'text',
        true
      );
    }).toThrow('Attribute expects a boolean as valuePerChannel');

    expect(() => {
      new MinimalAssetConcreteAttribute(
        createAssetFamilyIdentifier('designer'),
        createCode('brands'),
        createLabelCollection({en_US: 'Brands'}),
        'asset',
        true,
        false
      );
    }).toThrow('Attribute expects a AssetType argument');

    expect(() => {
      new MinimalAssetConcreteAttribute(
        createAssetFamilyIdentifier('designer'),
        createCode('brands'),
        createLabelCollection({en_US: 'Brands'}),
        'text',
        true,
        false
      );
    }).toThrow('MinimalAssetAttribute type needs to be "asset" or "asset_collection"');

    expect(() => {
      new MinimalConcreteAttribute(
        createAssetFamilyIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        'text'
      );
    }).toThrow('Attribute expects a boolean as valuePerLocale');
    expect(() => {
      new MinimalConcreteAttribute(
        createAssetFamilyIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'})
      );
    }).toThrow('Attribute expects a string as attribute type');
    expect(() => {
      new MinimalConcreteAttribute(createAssetFamilyIdentifier('designer'), createCode('description'));
    }).toThrow('Attribute expects a LabelCollection argument');
    expect(() => {
      new MinimalConcreteAttribute(createAssetFamilyIdentifier('designer'));
    }).toThrow('Attribute expects a AttributeCode argument');
    expect(() => {
      new MinimalConcreteAttribute();
    }).toThrow('Attribute expects an AssetFamilyIdentifier argument');
  });
});
