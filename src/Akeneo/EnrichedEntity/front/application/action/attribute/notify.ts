import notify from 'akeneoenrichedentity/application/event/notify';

export const notifyAttributeWellCreated = () => {
  return notify('success', 'pim_enriched_entity.attribute.notification.create.success');
};

export const notifyAttributeCreateFailed = () => {
  return notify('error', 'pim_enriched_entity.attribute.notification.create.fail');
};

export const notifyAttributeListUpdateFailed = () => {
  return notify('error', 'pim_enriched_entity.attribute.notification.list.fail');
};

export const notifyAttributeWellDeleted = () => {
  return notify('success', 'pim_enriched_entity.attribute.notification.delete.success');
};

export const notifyAttributeDeletionFailed = () => {
  return notify('error', 'pim_enriched_entity.attribute.notification.delete.fail');
};
