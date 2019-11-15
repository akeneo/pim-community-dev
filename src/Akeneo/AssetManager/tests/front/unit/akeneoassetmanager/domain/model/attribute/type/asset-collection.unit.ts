import {ConcreteAssetCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset-collection';
import {createAssetTypeFromNormalized} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';

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
    expect(ConcreteAssetCollectionAttribute.createFromNormalized(normalizedBrand).normalize()).toEqual(normalizedBrand);
  });
  test('I can create get a asset type', () => {
    expect(ConcreteAssetCollectionAttribute.createFromNormalized(normalizedBrand).getAssetType()).toEqual(
      createAssetTypeFromNormalized('brand')
    );
  });
});
