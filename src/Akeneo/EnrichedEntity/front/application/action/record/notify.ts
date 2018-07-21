import notify from 'akeneoenrichedentity/application/event/notify';

export const notifyRecordWellCreated = () => {
  return notify('success', 'pim_enriched_entity.record.notification.create.success');
};

export const notifyRecordCreateFailed = () => {
  return notify('success', 'pim_enriched_entity.record.notification.create.fail');
};
