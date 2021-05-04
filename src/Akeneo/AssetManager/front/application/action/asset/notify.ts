import notify from 'akeneoassetmanager/application/event/notify';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {ValidationError} from '@akeneo-pim-community/shared';

export const notifyAssetWellCreated = () => {
  return notify('success', 'pim_asset_manager.asset.notification.create.success');
};

export const notifyAssetCreateFailed = () => {
  return notify('error', 'pim_asset_manager.asset.notification.create.fail');
};

export const notifyAssetWellSaved = () => {
  return notify('success', 'pim_asset_manager.asset.notification.save.success');
};

export const notifyAssetSaveFailed = () => {
  return notify('error', 'pim_asset_manager.asset.notification.save.fail');
};

export const notifyAssetSaveValidationError = () => {
  return notify('error', 'pim_asset_manager.asset.notification.save.validation_error');
};

// TODO remove this when single asset deletion is implemented
export const notifyAssetWellDeleted = (assetCode: AssetCode) => {
  return notify('success', 'pim_asset_manager.asset.notification.delete.success', {
    code: assetCode,
  });
};

//TODO remove also
export const notifyAssetDeleteFailed = () => {
  return notify('error', 'pim_asset_manager.asset.notification.delete.fail');
};

//TODO remove also
export const notifyAssetDeletionErrorOccured = (errors: ValidationError[]) => {
  const firstError = errors[0];

  return notify('error', firstError.message);
};

export const notifyExecuteNamingConventionFailed = () => {
  return notify('error', 'pim_asset_manager.asset.notification.execute_naming_convention.fail');
};

export const notifyExecuteNamingConventionSuccess = () => {
  return notify('success', 'pim_asset_manager.asset.notification.execute_naming_convention.success');
};
