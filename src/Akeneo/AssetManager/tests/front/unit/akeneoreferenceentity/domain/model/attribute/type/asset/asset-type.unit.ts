import {AssetType} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';
import {createIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';

describe('akeneo > attribute > domain > model > attribute > type > asset --- asset type', () => {
  test('I can create a AssetType from normalized', () => {
    expect(AssetType.createFromNormalized('brand').normalize()).toEqual('brand');
    expect(AssetType.createFromNormalized(null).normalize()).toEqual(null);
    expect(() => new AssetType({my: 'object'})).toThrow();
  });

  test('I can validate a AssetType', () => {
    expect(AssetType.isValid('test')).toEqual(true);
    expect(AssetType.isValid(null)).toEqual(false);
    expect(AssetType.isValid(12)).toEqual(false);
    expect(AssetType.isValid(null)).toEqual(false);
    expect(AssetType.isValid({test: 'toto'})).toEqual(false);
  });

  test('I can create a AssetType from string', () => {
    expect(AssetType.createFromString('brand').stringValue()).toEqual('brand');
    expect(AssetType.createFromString('').stringValue()).toEqual('');
    expect(() => AssetType.createFromString({my: 'object'})).toThrow();
  });

  test('I can get the asset family identifier', () => {
    expect(AssetType.createFromString('brand').getAssetFamilyIdentifier()).toEqual(createIdentifier('brand'));
    expect(() => AssetType.createFromNormalized(null).getAssetFamilyIdentifier()).toThrow();
  });

  test('I can test if a asset type is equal to another one', () => {
    expect(AssetType.createFromString('brand').equals(AssetType.createFromString('brand'))).toBe(true);
    expect(AssetType.createFromString('brand').equals(AssetType.createFromString('designer'))).toBe(false);
    expect(AssetType.createFromNormalized(null).equals(AssetType.createFromString('designer'))).toBe(false);
    expect(AssetType.createFromNormalized(null).equals(AssetType.createFromNormalized(null))).toBe(true);
  });
});
