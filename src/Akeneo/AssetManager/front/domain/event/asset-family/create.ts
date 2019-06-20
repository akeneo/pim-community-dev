import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {NormalizedCode} from 'akeneoassetmanager/domain/model/code';
import {NormalizedLocaleCode} from 'akeneoassetmanager/domain/model/locale';

export const assetFamilyCreationStart = () => {
  return {type: 'ASSET_FAMILY_CREATION_START'};
};

export const assetFamilyCreationCodeUpdated = (value: NormalizedCode) => {
  return {type: 'ASSET_FAMILY_CREATION_CODE_UPDATED', value};
};

export const assetFamilyCreationLabelUpdated = (value: string, locale: NormalizedLocaleCode) => {
  return {type: 'ASSET_FAMILY_CREATION_LABEL_UPDATED', value, locale};
};

export const assetFamilyCreationCancel = () => {
  return {type: 'ASSET_FAMILY_CREATION_CANCEL'};
};

export const assetFamilyCreationSubmission = () => {
  return {type: 'ASSET_FAMILY_CREATION_SUBMISSION'};
};

export const assetFamilyCreationSucceeded = () => {
  return {type: 'ASSET_FAMILY_CREATION_SUCCEEDED'};
};

export const assetFamilyCreationErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ASSET_FAMILY_CREATION_ERROR_OCCURED', errors};
};
