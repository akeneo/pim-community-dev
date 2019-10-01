import {
  denormalizeAssetCode,
  assetcodesAreEqual,
  assetCodeStringValue,
} from 'akeneoassetmanager/domain/model/asset/code';

describe('akeneo > asset family > domain > model --- code', () => {
  test('I can create a new code with a string value', () => {
    expect(denormalizeAssetCode('michel')).toBe('michel');
  });

  test('I cannot create a new code with a value other than a string', () => {
    expect(() => {
      denormalizeAssetCode(12);
    }).toThrow('Code expects a string as parameter to be created');
  });

  test('I can compare two codes', () => {
    expect(assetcodesAreEqual(denormalizeAssetCode('michel'), denormalizeAssetCode('didier'))).toBe(false);
    expect(assetcodesAreEqual(denormalizeAssetCode('didier'), denormalizeAssetCode('didier'))).toBe(true);
  });

  test('It has a string representation', () => {
    expect(assetCodeStringValue(denormalizeAssetCode('michel'))).toBe('michel');
  });
});
