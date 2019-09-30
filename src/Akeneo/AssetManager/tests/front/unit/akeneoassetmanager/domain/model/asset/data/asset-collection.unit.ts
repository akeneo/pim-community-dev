import {create, denormalize} from 'akeneoassetmanager/domain/model/asset/data/asset-collection';

describe('akeneo > asset family > domain > model > asset > data --- asset collection', () => {
  test('I can create a new AssetData with a AssetCode collection value', () => {
    expect(create([]).normalize()).toEqual([]);
    expect(create(['starck']).normalize()).toEqual(['starck']);
    expect(create(['starck', 'dyson']).normalize()).toEqual(['starck', 'dyson']);
  });

  test('I cannot create a new AssetData with a value other than a AssetCode collection', () => {
    expect(() => {
      create(12);
    }).toThrow('AssetCollectionData expects an array of AssetCode as parameter to be created');
    expect(() => {
      create([12]);
    }).toThrow('AssetCollectionData expects an array of AssetCode as parameter to be created');
  });

  test('I can normalize a AssetData', () => {
    expect(denormalize(null).normalize()).toEqual([]);
    expect(denormalize(['starck']).normalize()).toEqual(['starck']);
    expect(denormalize(['starck', 'dyson']).normalize()).toEqual(['starck', 'dyson']);
  });

  test('I can test if two assetData are equal', () => {
    expect(denormalize(['starck']).equals(denormalize(['starck']))).toEqual(true);
    expect(denormalize(['starck', 'dyson']).equals(denormalize(['starck', 'dyson']))).toEqual(true);
    expect(denormalize(['dyson', 'starck']).equals(denormalize(['starck', 'dyson']))).toEqual(false);
    expect(denormalize(['starck']).equals(denormalize(['dyson']))).toEqual(false);
    expect(denormalize(['starck']).equals(['starck'])).toEqual(false);
    expect(denormalize(null).equals(denormalize(null))).toEqual(true);
    expect(denormalize(null).equals(denormalize(['dyson']))).toEqual(false);
  });

  test('I can test if the asset data is empty', () => {
    expect(denormalize(['starck']).isEmpty()).toEqual(false);
    expect(denormalize(null).isEmpty()).toEqual(true);
  });
});
