import {create, denormalize} from 'akeneoassetmanager/domain/model/asset/data/asset';
import {denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';

describe('akeneo > asset family > domain > model > asset > data --- asset', () => {
  test('I can create a new AssetData with a AssetCode value', () => {
    expect(create(denormalizeAssetCode('starck')).normalize()).toEqual('starck');
  });

  test('I cannot create a new AssetData with a value other than a AssetCode', () => {
    expect(() => {
      create(12);
    }).toThrow('Code expects a string as parameter to be created');
  });

  test('I can normalize a AssetData', () => {
    expect(denormalize('starck').normalize()).toEqual('starck');
    expect(denormalize(null).normalize()).toEqual(null);
  });

  test('I can test if two assetData are equal', () => {
    expect(denormalize('starck').equals(denormalize('starck'))).toEqual(true);
    expect(denormalize('starck').equals(denormalize('dyson'))).toEqual(false);
    expect(denormalize('starck').equals('starck')).toEqual(false);
    expect(denormalize(null).equals(denormalize(null))).toEqual(true);
    expect(denormalize(null).equals(denormalize('dyson'))).toEqual(false);
  });
});
