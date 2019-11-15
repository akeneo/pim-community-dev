import {ConcreteAssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';

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
  test('I can get the asset type from the attribute', () => {
    expect(ConcreteAssetAttribute.createFromNormalized(normalizedBrand).getAssetType()).toEqual('brand');
  });
});
