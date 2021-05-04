import notify from 'akeneoassetmanager/application/event/notify';
import {ValidationError} from '@akeneo-pim-community/shared';

export const notifyAssetFamilyWellSaved = () => {
  return notify('success', 'pim_asset_manager.asset_family.notification.save.success');
};

export const notifyAssetFamilySaveFailed = () => {
  return notify('error', 'pim_asset_manager.asset_family.notification.save.fail');
};

export const notifyLaunchComputeTransformationsFailed = () => {
  return notify('error', 'pim_asset_manager.asset_family.notification.compute_transformations.fail');
};

export const notifyLaunchComputeTransformationsSucceeded = () => {
  return notify('info', 'pim_asset_manager.asset_family.notification.compute_transformations.success');
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

export const notifyExecuteProductLinkRulesSucceeded = () => {
  return notify('success', 'pim_asset_manager.asset_family.notification.execute_product_link_rules.success');
};

export const notifyExecuteProductLinkRulesFailed = () => {
  return notify('error', 'pim_asset_manager.asset_family.notification.execute_product_link_rules.fail');
};

export const notifyExecuteNamingConventionSucceeded = () => {
  return notify('success', 'pim_asset_manager.asset_family.notification.execute_naming_convention.success');
};

export const notifyExecuteNamingConventionFailed = () => {
  return notify('error', 'pim_asset_manager.asset_family.notification.execute_naming_convention.fail');
};
