'use strict';

import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/line-factory';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/creation-asset';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';

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
    transformations: '[]',
    namingConvention: '',
    productLinkRules: '',
  });
};

export const createFakeLine = (
  filename: string,
  assetFamily: AssetFamily,
  channels: Channel[],
  locales: Locale[]
): Line => {
  return Object.freeze(createLineFromFilename(filename, assetFamily, channels, locales));
};

export const createFakeError = (message: string = 'error'): ValidationError => {
  return Object.freeze({
    messageTemplate: message,
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

export const createFakeLocale = (code: string, label?: string): Locale => {
  return Object.freeze({
    code: code,
    label: label || code,
    language: code,
    region: code,
  });
};

export const createFakeChannel = (code: string, locales: string[] = []): Channel => {
  return Object.freeze({
    code: code,
    labels: {en_US: code},
    locales: locales.map(locale => createFakeLocale(locale)),
  });
};
