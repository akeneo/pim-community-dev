import notify from 'akeneoassetmanager/application/event/notify';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';

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

export const notifyAssetWellDeleted = (assetCode: AssetCode) => {
  return notify('success', 'pim_asset_manager.asset.notification.delete.success', {
    code: assetCode,
  });
};

export const notifyAllAssetsWellDeleted = (assetFamilyIdentifier: AssetFamilyIdentifier) => {
  return notify('success', 'pim_asset_manager.asset.notification.delete_all.success', {
    entityIdentifier: assetFamilyIdentifier,
  });
};

export const notifyAllAssetsDeletionFailed = () => {
  return notify('error', 'pim_asset_manager.asset.notification.delete_all.failed');
};

export const notifyAssetDeleteFailed = () => {
  return notify('error', 'pim_asset_manager.asset.notification.delete.fail');
};

export const notifyAssetDeletionErrorOccured = (errors: ValidationError[]) => {
  const firstError = errors[0];

  return notify('error', firstError.message);
};
