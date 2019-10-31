import {denormalizeAssetIdentifier, assetidentifiersAreEqual} from 'akeneoassetmanager/domain/model/asset/identifier';

describe('akeneo > asset family > domain > model --- identifier', () => {
  test('I can create a new identifier with a string value', () => {
    expect(denormalizeAssetIdentifier('michel')).toBe('michel');
  });

  test('I can get the string value of an identifier', () => {
    expect(denormalizeAssetIdentifier('michel')).toBe('michel');
  });

  test('I cannot create a new identifier with a value for asset family identifier other than a string', () => {
    expect(() => {
      denormalizeAssetIdentifier(12);
    }).toThrow('Identifier expects a string as parameter to be created');
  });

  test('I can compare two identifiers', () => {
    expect(assetidentifiersAreEqual(denormalizeAssetIdentifier('michel'), denormalizeAssetIdentifier('didier'))).toBe(
      false
    );
    expect(assetidentifiersAreEqual(denormalizeAssetIdentifier('didier'), denormalizeAssetIdentifier('didier'))).toBe(
      true
    );
  });
});
