import {
  getEditionAssetCompleteness,
  createEmptyEditionAsset,
} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {createEmptyAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';

describe('akeneo > asset family > domain > model > asset --- edition-asset', () => {
  test('I can get the completeness of an asset', () => {
    expect(
      getEditionAssetCompleteness(
        {
          code: 'my_asset',
          values: [],
        },
        'ecommerce',
        'en_US'
      ).hasRequiredAttribute()
    ).toEqual(false);
  });
  test('I can generate an empty asset', () => {
    expect(createEmptyEditionAsset()).toEqual({
      identifier: '',
      code: '',
      labels: {},
      assetFamily: createEmptyAssetFamily(),
      values: [],
    });
  });
});
