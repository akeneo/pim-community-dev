import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {NormalizedValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';

export const createFakeAssetFamily = (valuePerLocale: boolean, valuePerChannel: boolean): AssetFamily => {
  return Object.freeze({
    identifier: 'packshot',
    code: 'packshot',
    labels: {en_US: 'Packshot'},
    image: null,
    attributeAsLabel: 'name',
    attributeAsMainMedia: 'picture_fingerprint',
    attributes: [
      {
        identifier: 'name',
        asset_family_identifier: 'name',
        code: 'name',
        type: 'text',
        labels: {en_US: 'Name'},
        order: 0,
        is_required: true,
        value_per_locale: false,
        value_per_channel: false,
      },
      {
        identifier: 'picture_fingerprint',
        asset_family_identifier: 'packshot',
        code: 'picture',
        type: 'media_file',
        labels: {en_US: 'Picture'},
        order: 0,
        is_required: true,
        value_per_locale: valuePerLocale,
        value_per_channel: valuePerChannel,
      },
    ],
  });
};

export const createFakeLine = (filename: string, assetFamily: AssetFamily): Line => {
  return Object.freeze(createLineFromFilename(filename, assetFamily));
};

export const createFakeError = (message: string = 'error'): NormalizedValidationError => {
  return Object.freeze({
    messageTemplate: '',
    parameters: {},
    message: message,
    propertyPath: '',
    invalidValue: null,
  });
};

export const createFakeCreationAsset = (code: string, assetFamily: AssetFamily): CreationAsset => {
  return Object.freeze({
    assetFamilyIdentifier: assetFamily.identifier,
    code: code,
    labels: {},
    values: [],
  });
};
