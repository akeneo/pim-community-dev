import {emptyAssetFamily} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset-family';

test('It could return an empty asset family', () => {
  const assetFamily = {
    identifier: '',
    code: '',
    labels: {},
    image: null,
    attributeAsLabel: '',
    attributeAsImage: '',
  };

  expect(emptyAssetFamily()).toMatchObject(assetFamily);
});
