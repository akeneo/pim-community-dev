import notify from 'akeneoreferenceentity/application/event/notify';

export const notifyAttributeWellSaved = () => {
  return notify('success', 'pim_reference_entity.attribute.notification.save.success');
};

export const notifyAttributeSaveFailed = () => {
  return notify('error', 'pim_reference_entity.attribute.notification.save.fail');
};

export const notifyAttributeSaveValidationError = () => {
  return notify('error', 'pim_reference_entity.attribute.notification.save.validation_error');
};

export const notifyAttributeWellCreated = () => {
  return notify('success', 'pim_reference_entity.attribute.notification.create.success');
};

export const notifyAttributeCreateFailed = () => {
  return notify('error', 'pim_reference_entity.attribute.notification.create.fail');
};

export const notifyAttributeCreateValidationError = () => {
  return notify('error', 'pim_reference_entity.attribute.notification.create.validation_error');
};

export const notifyAttributeListUpdateFailed = () => {
  return notify('error', 'pim_reference_entity.attribute.notification.list.fail');
};

export const notifyAttributeWellDeleted = () => {
  return notify('success', 'pim_reference_entity.attribute.notification.delete.success');
};

export const notifyAttributeDeletionFailed = () => {
  return notify('error', 'pim_reference_entity.attribute.notification.delete.fail');
};
