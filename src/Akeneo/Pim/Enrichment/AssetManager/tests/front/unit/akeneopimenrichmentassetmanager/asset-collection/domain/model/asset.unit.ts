import {
  getImage,
  isComplete,
  emptyAsset,
  isLabels,
  getAssetLabel,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';

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
