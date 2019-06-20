import notify from 'akeneoassetmanager/application/event/notify';

export const notifyAttributeWellSaved = () => {
  return notify('success', 'pim_asset_manager.attribute.notification.save.success');
};

export const notifyAttributeSaveFailed = () => {
  return notify('error', 'pim_asset_manager.attribute.notification.save.fail');
};

export const notifyAttributeSaveValidationError = () => {
  return notify('error', 'pim_asset_manager.attribute.notification.save.validation_error');
};

export const notifyAttributeWellCreated = () => {
  return notify('success', 'pim_asset_manager.attribute.notification.create.success');
};

export const notifyAttributeCreateFailed = () => {
  return notify('error', 'pim_asset_manager.attribute.notification.create.fail');
};

export const notifyAttributeCreateValidationError = () => {
  return notify('error', 'pim_asset_manager.attribute.notification.create.validation_error');
};

export const notifyAttributeListUpdateFailed = () => {
  return notify('error', 'pim_asset_manager.attribute.notification.list.fail');
};

export const notifyAttributeWellDeleted = () => {
  return notify('success', 'pim_asset_manager.attribute.notification.delete.success');
};

export const notifyAttributeDeletionFailed = () => {
  return notify('error', 'pim_asset_manager.attribute.notification.delete.fail');
};
