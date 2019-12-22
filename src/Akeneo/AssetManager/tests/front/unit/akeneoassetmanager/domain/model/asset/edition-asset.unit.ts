import {
  getEditionAssetCompleteness,
  createEmptyEditionAsset,
  getEditionAssetLabel,
} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {createEmptyAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';

const editionAsset = {
  code: 'my_asset',
  values: [],
  labels: {en_US: 'nice label'},
};

describe('akeneo > asset family > domain > model > asset --- edition-asset', () => {
  test('I can get the completeness of an asset', () => {
    expect(getEditionAssetCompleteness(editionAsset, 'ecommerce', 'en_US').hasRequiredAttribute()).toEqual(false);
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

  test('I can get the label for a EditionAsset', () => {
    expect(getEditionAssetLabel(editionAsset, 'en_US')).toEqual('nice label');
  });
});
