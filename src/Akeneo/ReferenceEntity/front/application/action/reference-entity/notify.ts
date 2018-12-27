import notify from 'akeneoreferenceentity/application/event/notify';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';

export const notifyReferenceEntityWellSaved = () => {
  return notify('success', 'pim_reference_entity.reference_entity.notification.save.success');
};

export const notifyReferenceEntitySaveFailed = () => {
  return notify('error', 'pim_reference_entity.reference_entity.notification.save.fail');
};

export const notifyReferenceEntityWellCreated = () => {
  return notify('success', 'pim_reference_entity.reference_entity.notification.create.success');
};

export const notifyReferenceEntityCreateFailed = () => {
  return notify('error', 'pim_reference_entity.reference_entity.notification.create.fail');
};

export const notifyReferenceEntityWellDeleted = () => {
  return notify('success', 'pim_reference_entity.reference_entity.notification.delete.success');
};

export const notifyReferenceEntityDeleteFailed = () => {
  return notify('error', 'pim_reference_entity.reference_entity.notification.delete.fail');
};

export const notifyReferenceEntityDeletionErrorOccured = (errors: ValidationError[]) => {
  const firstError = errors[0];

  return notify('error', firstError.message);
};

export const notifyPermissionWellSaved = () => {
  return notify('success', 'pim_reference_entity.permission.notification.save.success');
};

export const notifyPermissionSaveFailed = () => {
  return notify('error', 'pim_reference_entity.permission.notification.save.fail');
};
