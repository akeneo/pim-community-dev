import {ValidationError} from '@akeneo-pim-community/shared';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import EditionAsset from 'akeneoassetmanager/domain/model/asset/edition-asset';

export const assetEditionReceived = (asset: EditionAsset) => {
  return {type: 'ASSET_EDITION_RECEIVED', asset};
};

export const assetEditionUpdated = (asset: EditionAsset) => {
  return {type: 'ASSET_EDITION_UPDATED', asset};
};

export const assetEditionValueUpdated = (value: EditionValue) => {
  return {type: 'ASSET_EDITION_VALUE_UPDATED', value};
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
