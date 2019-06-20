import {ConcreteAssetCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset-collection';
import {createIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {createIdentifier as createAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {createCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {AssetType} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';

const normalizedBrand = {
  identifier: 'brand',
  asset_family_identifier: 'designer',
  code: 'brand',
  labels: {en_US: 'Brand'},
  type: 'asset_collection',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  asset_type: 'brand',
};

describe('akeneo > attribute > domain > model > attribute > type --- AssetCollectionAttribute', () => {
  test('I can create a ConcreteAssetCollectionAttribute from normalized', () => {
    expect(ConcreteAssetCollectionAttribute.createFromNormalized(normalizedBrand).normalize()).toEqual(
      normalizedBrand
    );
  });
  test('I can create get a asset type', () => {
    expect(ConcreteAssetCollectionAttribute.createFromNormalized(normalizedBrand).getAssetType()).toEqual(
      AssetType.createFromString('brand')
    );
  });
  test('I cannot create an invalid ConcreteAssetCollectionAttribute', () => {
    expect(() => {
      new ConcreteAssetCollectionAttribute(
        createIdentifier('designer', 'brand'),
        createAssetFamilyIdentifier('designer'),
        createCode('brand'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0,
        true
      );
    }).toThrow('Attribute expects a AssetType as assetType');
  });
});
