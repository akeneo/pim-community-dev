import notify from 'akeneoenrichedentity/application/event/notify';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

export const notifyEnrichedEntityWellSaved = () => {
  return notify('success', 'pim_enriched_entity.enriched_entity.notification.save.success');
};

export const notifyEnrichedEntitySaveFailed = () => {
  return notify('error', 'pim_enriched_entity.enriched_entity.notification.save.fail');
};

export const notifyEnrichedEntityWellCreated = () => {
  return notify('success', 'pim_enriched_entity.enriched_entity.notification.create.success');
};

export const notifyEnrichedEntityCreateFailed = () => {
  return notify('error', 'pim_enriched_entity.enriched_entity.notification.create.fail');
};

export const notifyEnrichedEntityWellDeleted = () => {
  return notify('success', 'pim_enriched_entity.enriched_entity.notification.delete.success');
};

export const notifyEnrichedEntityDeleteFailed = () => {
  return notify('error', 'pim_enriched_entity.enriched_entity.notification.delete.fail');
};

export const notifyEnrichedEntityDeletionErrorOccured = (errors: ValidationError[]) => {
  const firstError = errors[0];

  return notify('error', firstError.message);
};
