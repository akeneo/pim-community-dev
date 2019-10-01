import {
  getImage,
  isComplete,
  emptyAsset,
  getAssetLabel,
  removeAssetFromCollection,
  emptyCollection,
  addAssetToCollection,
  MoveDirection,
  moveAssetInCollection,
  getAssetCodes,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {isLabels} from 'akeneoassetmanager/domain/model/utils';

test('It should get the image from the asset', () => {
  const image = '/rest/asset/image/thumbnail/iphone.jpg';
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    image,
    assetFamily: {
      identifier: 'packshot',
      code: 'packshot',
      labels: {en_US: 'Packshot'},
      image: null,
      attributeAsLabel: null,
      attributeAsImage: null,
    },
    labels: {en_US: 'Iphone'},
    completeness: {
      complete: 2,
      required: 3,
    },
  };

  expect(getImage(asset)).toEqual(image);
});

test('The asset is complete', () => {
  const complete = {complete: 2, required: 2};
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    image: '/rest/asset/image/thumbnail/iphone.jpg',
    assetFamily: {
      identifier: 'packshot',
      code: 'packshot',
      labels: {en_US: 'Packshot'},
      image: null,
      attributeAsLabel: null,
      attributeAsImage: null,
    },
    labels: {en_US: 'Iphone'},
    completeness: complete,
  };

  expect(isComplete(asset)).toEqual(true);
});

test('The asset is incomplete', () => {
  const incomplete = {complete: 2, required: 3};
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    image: '/rest/asset/image/thumbnail/iphone.jpg',
    assetFamily: {
      identifier: 'packshot',
      code: 'packshot',
      labels: {en_US: 'Packshot'},
      image: null,
      attributeAsLabel: null,
      attributeAsImage: null,
    },
    labels: {en_US: 'Iphone'},
    completeness: incomplete,
  };

  expect(isComplete(asset)).toEqual(false);
});

test('It could return an empty asset', () => {
  const asset = {
    identifier: '',
    code: '',
    image: '',
    assetFamily: {
      identifier: '',
      code: '',
      labels: {},
      image: {
        filePath: '',
        originalFilename: '',
      },
      attributeAsLabel: '',
      attributeAsImage: '',
    },
    labels: {},
    completeness: {
      complete: 0,
      required: 0,
    },
  };

  expect(emptyAsset()).toMatchObject(asset);
});

test('It could validate if the label object is well formated', () => {
  const stringLabel = 'Packshot';
  expect(isLabels(stringLabel)).toEqual(false);
  expect(isLabels(undefined)).toEqual(false);

  const wrongFormatedLabels = {en_US: 123};
  expect(isLabels(wrongFormatedLabels)).toEqual(false);

  const labels = {en_US: 'Packshot'};
  expect(isLabels(labels)).toEqual(true);
});

test('I should get a label from my asset', () => {
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    labels: {en_US: 'Iphone'},
  };

  expect(getAssetLabel(asset, 'en_US')).toEqual('Iphone');
  expect(getAssetLabel(asset, 'fr_FR')).toEqual('[iphone]');
});

test('I should be able to remove an asset from the collection', () => {
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    labels: {en_US: 'Iphone'},
  };

  expect(removeAssetFromCollection(['samsung', 'oneplus', 'iphone'], asset)).toEqual(['samsung', 'oneplus']);
});

test('I should be able to empty the collection', () => {
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    labels: {en_US: 'Iphone'},
  };

  expect(emptyCollection(['samsung', 'oneplus', 'iphone'])).toEqual([]);
});

test('It should add assets in the collection', () => {
  const assetCollection = ['samsung', 'oneplus'];
  const assetCodes = ['honor', 'iphone'];

  expect(addAssetToCollection(assetCollection, assetCodes)).toEqual(['samsung', 'oneplus', 'honor', 'iphone']);
});

test('I should be able to move assets', () => {
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    labels: {en_US: 'Iphone'},
  };

  expect(moveAssetInCollection(['samsung', 'oneplus', 'iphone', 'huawei'], asset, MoveDirection.Before)).toEqual([
    'samsung',
    'iphone',
    'oneplus',
    'huawei',
  ]);

  expect(moveAssetInCollection(['samsung', 'oneplus', 'iphone', 'huawei'], asset, MoveDirection.After)).toEqual([
    'samsung',
    'oneplus',
    'huawei',
    'iphone',
  ]);

  expect(moveAssetInCollection(['samsung', 'oneplus', 'huawei', 'iphone'], asset, MoveDirection.Before)).toEqual([
    'samsung',
    'oneplus',
    'iphone',
    'huawei',
  ]);
});

test("I should not be able to move the last asset after it's current position", () => {
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    labels: {en_US: 'Iphone'},
  };

  expect(moveAssetInCollection(['samsung', 'oneplus', 'iphone'], asset, MoveDirection.After)).toEqual([
    'samsung',
    'oneplus',
    'iphone',
  ]);
});

test("I should not be able to move the first asset before it's current position", () => {
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    labels: {en_US: 'Iphone'},
  };

  expect(moveAssetInCollection(['iphone', 'oneplus', 'samsung'], asset, MoveDirection.Before)).toEqual([
    'iphone',
    'oneplus',
    'samsung',
  ]);
});

test('I can get asset codes of a collection', () => {
  const iphone = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    labels: {en_US: 'Iphone'},
  };
  const samsung = {
    identifier: 'packshot_samsung_fingerprint',
    code: 'samsung',
    labels: {en_US: 'Samsung'},
  };
  expect(getAssetCodes([iphone, samsung])).toEqual(['iphone', 'samsung']);
});
