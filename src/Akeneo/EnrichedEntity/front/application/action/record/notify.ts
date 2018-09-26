import notify from 'akeneoenrichedentity/application/event/notify';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

export const notifyRecordWellCreated = () => {
  return notify('success', 'pim_enriched_entity.record.notification.create.success');
};

export const notifyRecordCreateFailed = () => {
  return notify('error', 'pim_enriched_entity.record.notification.create.fail');
};

export const notifyRecordWellSaved = () => {
  return notify('success', 'pim_enriched_entity.record.notification.save.success');
};

export const notifyRecordSaveFailed = () => {
  return notify('error', 'pim_enriched_entity.record.notification.save.fail');
};

export const notifyRecordWellDeleted = () => {
  return notify('success', 'pim_enriched_entity.record.notification.delete.success');
};

export const notifyRecordDeleteFailed = () => {
  return notify('error', 'pim_enriched_entity.record.notification.delete.fail');
};

export const notifyRecordDeletionErrorOccured = (errors: ValidationError[]) => {
  const firstError = errors[0];

  return notify('error', firstError.message);
};
