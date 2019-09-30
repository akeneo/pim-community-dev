import {ConcreteAssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {AssetType} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';

const normalizedBrand = {
  identifier: 'brand',
  asset_family_identifier: 'designer',
  code: 'brand',
  labels: {en_US: 'Brand'},
  type: 'asset',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  asset_type: 'brand',
};

describe('akeneo > attribute > domain > model > attribute > type --- AssetAttribute', () => {
  test('I can create a ConcreteAssetAttribute from normalized', () => {
    expect(ConcreteAssetAttribute.createFromNormalized(normalizedBrand).normalize()).toEqual(normalizedBrand);
  });
  test('I can create get a asset type', () => {
    expect(ConcreteAssetAttribute.createFromNormalized(normalizedBrand).getAssetType()).toEqual(
      AssetType.createFromString('brand')
    );
  });
  test('I cannot create an invalid ConcreteAssetAttribute', () => {
    expect(() => {
      new ConcreteAssetAttribute(
        'brand',
        'designer',
        'brand',
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0,
        true
      );
    }).toThrow('Attribute expects a AssetType as assetType');
  });
});
