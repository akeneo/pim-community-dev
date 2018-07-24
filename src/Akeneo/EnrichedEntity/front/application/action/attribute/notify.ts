import notify from 'akeneoenrichedentity/application/event/notify';

export const notifyAttributeWellCreated = () => {
  return notify('success', 'pim_enriched_entity.attribute.notification.create.success');
};

export const notifyAttributeCreateFailed = () => {
  return notify('error', 'pim_enriched_entity.attribute.notification.create.fail');
};
