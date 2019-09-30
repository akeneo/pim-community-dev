import {
  denormalizeMinimalAttribute,
  MinimalConcreteAttribute,
  MinimalAssetConcreteAttribute,
} from 'akeneoassetmanager/domain/model/attribute/minimal';
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
    expect(description.getAssetFamilyIdentifier()).toEqual('designer');
    expect(description.getCode()).toEqual('description');
    expect(description.getType()).toEqual('text');
    expect(description.getLabel('en_US')).toEqual('Description');
    expect(description.getLabel('fr_fr')).toEqual('[description]');
    expect(description.getLabel('fr_fr', false)).toEqual('');
    expect(description.getLabelCollection()).toEqual(createLabelCollection({en_US: 'Description'}));
  });
  test('I can create a new asset attribute with a identifier and labels', () => {
    expect(brands.getAssetFamilyIdentifier()).toEqual('designer');
    expect(brands.getCode()).toEqual('brands');
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
        'designer',
        'front_view',
        createLabelCollection({en_US: 'Front View'}),
        'text',
        true
      );
    }).toThrow('Attribute expects a boolean as valuePerChannel');

    expect(() => {
      new MinimalAssetConcreteAttribute(
        'designer',
        'brands',
        createLabelCollection({en_US: 'Brands'}),
        'asset',
        true,
        false
      );
    }).toThrow('Attribute expects a AssetType argument');

    expect(() => {
      new MinimalAssetConcreteAttribute(
        'designer',
        'brands',
        createLabelCollection({en_US: 'Brands'}),
        'text',
        true,
        false
      );
    }).toThrow('MinimalAssetAttribute type needs to be "asset" or "asset_collection"');

    expect(() => {
      new MinimalConcreteAttribute('designer', 'front_view', createLabelCollection({en_US: 'Front View'}), 'text');
    }).toThrow('Attribute expects a boolean as valuePerLocale');
    expect(() => {
      new MinimalConcreteAttribute('designer', 'front_view', createLabelCollection({en_US: 'Front View'}));
    }).toThrow('Attribute expects a string as attribute type');
    expect(() => {
      new MinimalConcreteAttribute('designer', 'description');
    }).toThrow('Attribute expects a LabelCollection argument');
  });
});
