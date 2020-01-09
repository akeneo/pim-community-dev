import {
  ASSET_COLLECTION_LIMIT,
  isComplete,
  getAssetLabel,
  removeAssetFromCollection,
  emptyCollection,
  getPreviousAssetCode,
  getNextAssetCode,
  canAddAssetToCollection,
  addAssetToCollection,
  addAssetsToCollection,
  isAssetInCollection,
  MoveDirection,
  moveAssetInCollection,
  getAssetCodes,
  getAssetByCode,
  sortAssetCollection,
  isMainMediaEmpty,
  assetHasCompleteness,
  createEmptyAsset,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import {isLabels} from 'akeneoassetmanager/domain/model/utils';

test('The asset is complete', () => {
  const complete = {complete: 2, required: 2};
  const asset = {
    identifier: 'packshot_iphone_fingerprint',
    code: 'iphone',
    image: '/rest/asset/image/thumbnail/iphone.jpg',
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
    labels: {en_US: 'Iphone'},
    completeness: incomplete,
  };

  expect(isComplete(asset)).toEqual(false);
});

test('It could return an empty asset', () => {
  const asset = {
    identifier: '',
    code: '',
    image: [],

    labels: {},
    completeness: {
      complete: 0,
      required: 0,
    },
  };

  expect(createEmptyAsset()).toMatchObject(asset);
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
  const assetCodeToRemove = 'iphone';
  expect(removeAssetFromCollection(['samsung', 'oneplus', 'iphone'], assetCodeToRemove)).toEqual([
    'samsung',
    'oneplus',
  ]);
});

test('I should be able to tell if an asset is in the collection', () => {
  expect(isAssetInCollection('iphone', ['samsung', 'oneplus', 'iphone'])).toEqual(true);
  expect(isAssetInCollection('UNKNOWN_ASSET_CODE', ['samsung', 'oneplus', 'iphone'])).toEqual(false);
});

test('I should be able to empty the collection', () => {
  expect(emptyCollection(['samsung', 'oneplus', 'iphone'])).toEqual([]);
});

test('I should be able to get the previous asset code in the collection', () => {
  const assetCollection = ['samsung', 'oneplus', 'honor'];

  let currentAssetCode = 'oneplus';
  expect(getPreviousAssetCode(assetCollection, currentAssetCode)).toEqual('samsung');

  currentAssetCode = 'samsung';
  expect(getPreviousAssetCode(assetCollection, currentAssetCode)).toEqual('honor');

  currentAssetCode = 'honor';
  expect(getPreviousAssetCode(assetCollection, currentAssetCode)).toEqual('oneplus');
});

test('I should be able to get the next asset code in the collection', () => {
  const assetCollection = ['samsung', 'oneplus', 'honor'];

  let currentAssetCode = 'oneplus';
  expect(getNextAssetCode(assetCollection, currentAssetCode)).toEqual('honor');

  currentAssetCode = 'samsung';
  expect(getNextAssetCode(assetCollection, currentAssetCode)).toEqual('oneplus');

  currentAssetCode = 'honor';
  expect(getNextAssetCode(assetCollection, currentAssetCode)).toEqual('samsung');
});

test('I should be able to tell if I can add an asset to the collection', () => {
  const assetCollection = ['oneplus'];
  const fullAssetCollection = new Array(ASSET_COLLECTION_LIMIT).fill('iphone');

  expect(canAddAssetToCollection(assetCollection)).toBe(true);
  expect(canAddAssetToCollection(fullAssetCollection)).toBe(false);
});

test('It should add asset in the collection', () => {
  const assetCollection = ['samsung', 'oneplus'];
  const assetCodeToAdd = 'honor';

  expect(addAssetToCollection(assetCollection, assetCodeToAdd)).toEqual(['samsung', 'oneplus', 'honor']);
});

test('It should add assets in the collection', () => {
  const assetCollection = ['samsung', 'oneplus'];
  const assetCodes = ['honor', 'iphone'];

  expect(addAssetsToCollection(assetCollection, assetCodes)).toEqual(['samsung', 'oneplus', 'honor', 'iphone']);
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

test('I should not be able to move the last asset after its current position', () => {
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

test('I should not be able to move the first asset before its current position', () => {
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

test('I can find an asset from a collection with its code', () => {
  const assetCollection = [
    {
      identifier: 'packshot_iphone_fingerprint',
      code: 'iphone',
      labels: {en_US: 'Iphone'},
    },
    {
      identifier: 'packshot_honor_fingerprint',
      code: 'honor',
      labels: {en_US: 'Honor'},
    },
  ];
  const expectedAsset = {
    identifier: 'packshot_honor_fingerprint',
    code: 'honor',
    labels: {en_US: 'Honor'},
  };

  expect(getAssetByCode(assetCollection, 'honor')).toMatchObject(expectedAsset);
});

test('I can sort an asset collection by codes when the order is already valid', () => {
  const assetCodes = ['honor', 'iphone'];
  const assetCollection = [
    {
      identifier: 'packshot_honor_fingerprint',
      code: 'honor',
      labels: {en_US: 'Honor'},
    },
    {
      identifier: 'packshot_iphone_fingerprint',
      code: 'iphone',
      labels: {en_US: 'Iphone'},
    },
  ];

  expect(sortAssetCollection(assetCollection, assetCodes)).toEqual(assetCollection);
});

test('I can sort an asset collection by codes when the order is invalid', () => {
  const assetCodes = ['honor', 'iphone'];
  const assetCollection = [
    {
      identifier: 'packshot_iphone_fingerprint',
      code: 'iphone',
      labels: {en_US: 'Iphone'},
    },
    {
      identifier: 'packshot_honor_fingerprint',
      code: 'honor',
      labels: {en_US: 'Honor'},
    },
  ];
  const expectedAssetCollection = [
    {
      identifier: 'packshot_honor_fingerprint',
      code: 'honor',
      labels: {en_US: 'Honor'},
    },
    {
      identifier: 'packshot_iphone_fingerprint',
      code: 'iphone',
      labels: {en_US: 'Iphone'},
    },
  ];

  expect(sortAssetCollection(assetCollection, assetCodes)).toEqual(expectedAssetCollection);
});

test('I can know if the asset has a completeness', () => {
  const asset = {
    identifier: '',
    code: '',
    image: [],
    assetFamily: {},
    completeness: {
      required: 10,
      complete: 4,
    },
  };
  expect(assetHasCompleteness(asset)).toBe(true);
  const noCompletenessAsset = {
    identifier: '',
    code: '',
    image: [],
    assetFamily: {},
    completeness: {
      required: 0,
      complete: 0,
    },
  };
  expect(assetHasCompleteness(noCompletenessAsset)).toBe(false);
});

test('I can tell if the ListAsset main media is empty for a given channel & locale', () => {
  const emptyAsset = {
    identifier: '',
    code: '',
    image: [],
    assetFamily: {},
    completeness: {
      required: 10,
      complete: 4,
    },
  };
  const notEmptyAsset = {
    ...emptyAsset,
    image: [
      {
        channel: 'ecommerce',
        locale: 'en_US',
        data: 'image.jpg',
      },
    ],
  };
  expect(isMainMediaEmpty(emptyAsset, 'ecommerce', 'en_US')).toBe(true);
  expect(isMainMediaEmpty(notEmptyAsset, 'ecommerce', 'en_US')).toBe(false);
});
