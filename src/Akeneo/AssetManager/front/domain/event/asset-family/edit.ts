import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {File} from 'akeneoassetmanager/domain/model/file';

export const assetFamilyEditionReceived = (assetFamily: AssetFamily) => {
  return {type: 'ASSET_FAMILY_EDITION_RECEIVED', assetFamily};
};

export const assetFamilyAssetCountUpdated = (assetCount: number) => {
  return {type: 'GRID_TOTAL_COUNT_UPDATED', totalCount: assetCount};
};

export const assetFamilyEditionUpdated = (assetFamily: AssetFamily) => {
  return {type: 'ASSET_FAMILY_EDITION_UPDATED', assetFamily};
};

export const assetFamilyEditionLabelUpdated = (value: string, locale: string) => {
  return {type: 'ASSET_FAMILY_EDITION_LABEL_UPDATED', value, locale};
};

export const assetFamilyEditionImageUpdated = (image: File) => {
  return {type: 'ASSET_FAMILY_EDITION_IMAGE_UPDATED', image: image};
};

export const assetFamilyEditionSubmission = () => {
  return {type: 'ASSET_FAMILY_EDITION_SUBMISSION'};
};

export const assetFamilyEditionSucceeded = () => {
  return {type: 'ASSET_FAMILY_EDITION_SUCCEEDED'};
};

export const assetFamilyEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ASSET_FAMILY_EDITION_ERROR_OCCURED', errors};
};
