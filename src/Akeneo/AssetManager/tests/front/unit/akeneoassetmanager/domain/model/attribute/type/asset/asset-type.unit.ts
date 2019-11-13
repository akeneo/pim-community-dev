import {
  createAssetTypeFromNormalized,
  assetTypeStringValue,
  isValidAssetType,
  assetTypeAreEqual,
  assetTypeIsEmpty,
} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';

describe('akeneo > attribute > domain > model > attribute > type > asset --- asset type', () => {
  test('I can create a AssetType from normalized', () => {
    expect(createAssetTypeFromNormalized('brand')).toEqual('brand');
    expect(createAssetTypeFromNormalized(null)).toEqual(null);
  });

  test('I can validate a AssetType', () => {
    expect(isValidAssetType('test')).toEqual(true);
    expect(isValidAssetType(null)).toEqual(false);
    expect(isValidAssetType(12)).toEqual(false);
    expect(isValidAssetType(null)).toEqual(false);
    expect(isValidAssetType({test: 'toto'})).toEqual(false);
  });

  test('I can create a AssetType from string', () => {
    expect(assetTypeStringValue(createAssetTypeFromNormalized('brand'))).toEqual('brand');
    expect(assetTypeStringValue(createAssetTypeFromNormalized(''))).toEqual('');
    expect(() => createAssetTypeFromNormalized({my: 'object'})).toThrow();
  });

  test('I can get the asset family identifier', () => {
    expect(createAssetTypeFromNormalized('brand')).toEqual('brand');
  });
  test('I can test if an asset type is empty', () => {
    expect(assetTypeIsEmpty(createAssetTypeFromNormalized('brand'))).toEqual(false);
    expect(assetTypeIsEmpty(createAssetTypeFromNormalized(null))).toEqual(true);
  });

  test('I can test if a asset type is equal to another one', () => {
    expect(assetTypeAreEqual(createAssetTypeFromNormalized('brand'), createAssetTypeFromNormalized('brand'))).toBe(
      true
    );
    expect(assetTypeAreEqual(createAssetTypeFromNormalized('brand'), createAssetTypeFromNormalized('designer'))).toBe(
      false
    );
    expect(assetTypeAreEqual(createAssetTypeFromNormalized(null), createAssetTypeFromNormalized('designer'))).toBe(
      false
    );
    expect(assetTypeAreEqual(createAssetTypeFromNormalized(null), createAssetTypeFromNormalized(null))).toBe(true);
  });
});
