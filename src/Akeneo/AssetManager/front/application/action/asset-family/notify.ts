import notify from 'akeneoassetmanager/application/event/notify';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';

export const notifyAssetFamilyWellSaved = () => {
  return notify('success', 'pim_asset_manager.asset_family.notification.save.success');
};

export const notifyAssetFamilySaveFailed = () => {
  return notify('error', 'pim_asset_manager.asset_family.notification.save.fail');
};

export const notifyAssetFamilyWellCreated = () => {
  return notify('success', 'pim_asset_manager.asset_family.notification.create.success');
};

export const notifyAssetFamilyCreateFailed = () => {
  return notify('error', 'pim_asset_manager.asset_family.notification.create.fail');
};

export const notifyAssetFamilyWellDeleted = () => {
  return notify('success', 'pim_asset_manager.asset_family.notification.delete.success');
};

export const notifyAssetFamilyDeleteFailed = () => {
  return notify('error', 'pim_asset_manager.asset_family.notification.delete.fail');
};

export const notifyAssetFamilyDeletionErrorOccured = (errors: ValidationError[]) => {
  const firstError = errors[0];

  return notify('error', firstError.message);
};

export const notifyPermissionWellSaved = () => {
  return notify('success', 'pim_asset_manager.permission.notification.save.success');
};

export const notifyPermissionSaveFailed = () => {
  return notify('error', 'pim_asset_manager.permission.notification.save.fail');
};
