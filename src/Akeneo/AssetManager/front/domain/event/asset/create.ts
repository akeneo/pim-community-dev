import ValidationError from 'akeneoassetmanager/domain/model/validation-error';

export const assetCreationStart = () => {
  return {type: 'ASSET_CREATION_START'};
};

export const assetCreationAssetCodeUpdated = (value: string) => {
  return {type: 'ASSET_CREATION_ASSET_CODE_UPDATED', value};
};

export const assetCreationLabelUpdated = (value: string, locale: string) => {
  return {type: 'ASSET_CREATION_LABEL_UPDATED', value, locale};
};

export const assetCreationCancel = () => {
  return {type: 'ASSET_CREATION_CANCEL'};
};

export const assetCreationSubmission = () => {
  return {type: 'ASSET_CREATION_SUBMISSION'};
};

export const assetCreationSucceeded = () => {
  return {type: 'ASSET_CREATION_SUCCEEDED'};
};

export const assetCreationErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ASSET_CREATION_ERROR_OCCURED', errors};
};
