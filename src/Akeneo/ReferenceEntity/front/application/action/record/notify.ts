import notify from 'akeneoreferenceentity/application/event/notify';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';

export const notifyRecordWellCreated = () => {
  return notify('success', 'pim_reference_entity.record.notification.create.success');
};

export const notifyRecordCreateFailed = () => {
  return notify('error', 'pim_reference_entity.record.notification.create.fail');
};

export const notifyRecordWellSaved = () => {
  return notify('success', 'pim_reference_entity.record.notification.save.success');
};

export const notifyRecordSaveFailed = () => {
  return notify('error', 'pim_reference_entity.record.notification.save.fail');
};

export const notifyRecordWellDeleted = (recordCode: RecordCode) => {
  return notify('success', 'pim_reference_entity.record.notification.delete.success', {code: recordCode.stringValue()});
};

export const notifyAllRecordsWellDeleted = (referenceEntityIdentifier: ReferenceEntityIdentifier) => {
  return notify('success', 'pim_reference_entity.record.notification.delete_all.success', {
    entityIdentifier: referenceEntityIdentifier.stringValue(),
  });
};

export const notifyAllRecordsDeletionFailed = () => {
  return notify('error', 'pim_reference_entity.record.notification.delete_all.failed');
};

export const notifyRecordDeleteFailed = () => {
  return notify('error', 'pim_reference_entity.record.notification.delete.fail');
};

export const notifyRecordDeletionErrorOccured = (errors: ValidationError[]) => {
  const firstError = errors[0];

  return notify('error', firstError.message);
};
