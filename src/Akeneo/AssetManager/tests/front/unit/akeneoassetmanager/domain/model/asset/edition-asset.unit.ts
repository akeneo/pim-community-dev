import {
  getEditionAssetCompleteness,
  createEmptyEditionAsset,
  getEditionAssetLabel,
  getEditionAssetMainMediaThumbnail,
} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {createEmptyAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';

const assetFamily = {attributeAsMainMedia: 'front_view'};
const mediaFileAttribute = {
  identifier: 'front_view',
  asset_family_identifier: 'designer',
  code: 'front_view',
  labels: {en_US: 'Front view'},
  type: 'media_file',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_file_size: null,
  allowed_extensions: [],
};
const mediaFileData = {
  filePath: 'path/to/file',
  originalFilename: 'original_name.jpg',
};
const mediaFileValue = {
  attribute: mediaFileAttribute,
  channel: 'ecommerce',
  locale: 'en_US',
  data: mediaFileData,
};
const editionAsset = {
  code: 'my_asset',
  assetFamily,
  values: [mediaFileValue],
  labels: {en_US: 'nice label'},
};

describe('akeneo > asset family > domain > model > asset --- edition-asset', () => {
  test('I can get the completeness of an asset', () => {
    expect(getEditionAssetCompleteness(editionAsset, 'ecommerce', 'en_US').hasRequiredAttribute()).toEqual(true);
    expect(getEditionAssetCompleteness(editionAsset, 'other-channel', 'fr_FR').hasRequiredAttribute()).toEqual(false);
  });

  test('I can generate an empty asset', () => {
    expect(createEmptyEditionAsset()).toEqual({
      identifier: '',
      code: '',
      labels: {},
      createdAt: '',
      updatedAt: '',
      assetFamily: createEmptyAssetFamily(),
      values: [],
    });
  });

  test('I can get the label for a EditionAsset', () => {
    expect(getEditionAssetLabel(editionAsset, 'en_US')).toEqual('nice label');
  });

  test('I can get the EditionAsset MainMediaThumbnail', () => {
    const expectedMediaPreview = {
      type: 'thumbnail',
      attributeIdentifier: mediaFileAttribute.identifier,
      data: mediaFileData.filePath,
    };
    expect(getEditionAssetMainMediaThumbnail(editionAsset, 'ecommerce', 'en_US')).toEqual(expectedMediaPreview);
  });

  test('I get an empty preview if the EditionAsset values are empty', () => {
    const emptyEditionAsset = {...editionAsset, values: []};
    const expectedMediaPreview = {
      type: 'thumbnail',
      attributeIdentifier: 'UNKNOWN',
      data: '',
    };
    expect(getEditionAssetMainMediaThumbnail(emptyEditionAsset, 'ecommerce', 'en_US')).toEqual(expectedMediaPreview);
  });
});
