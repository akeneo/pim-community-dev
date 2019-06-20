import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import File from 'akeneoassetmanager/domain/model/file';
import Asset from 'akeneoassetmanager/domain/model/asset/asset';
import Value from 'akeneoassetmanager/domain/model/asset/value';

export const assetEditionReceived = (asset: Asset) => {
  return {type: 'ASSET_EDITION_RECEIVED', asset: asset.normalize()};
};

export const assetEditionUpdated = (asset: Asset) => {
  return {type: 'ASSET_EDITION_UPDATED', asset};
};

export const assetEditionLabelUpdated = (label: string, locale: string) => {
  return {type: 'ASSET_EDITION_LABEL_UPDATED', label, locale};
};

export const assetEditionImageUpdated = (image: File) => {
  return {type: 'ASSET_EDITION_IMAGE_UPDATED', image: image.normalize()};
};

export const assetEditionValueUpdated = (value: Value) => {
  return {type: 'ASSET_EDITION_VALUE_UPDATED', value: value.normalize()};
};

export const assetEditionSubmission = () => {
  return {type: 'ASSET_EDITION_SUBMISSION'};
};

export const assetEditionSucceeded = () => {
  return {type: 'ASSET_EDITION_SUCCEEDED'};
};

export const assetEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ASSET_EDITION_ERROR_OCCURED', errors};
};
