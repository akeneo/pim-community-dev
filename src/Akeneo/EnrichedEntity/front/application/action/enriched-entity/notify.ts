import notify from 'akeneoenrichedentity/application/event/notify';

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
  return notify('success', 'pim_enriched_entity.enriched_entity.notification.create.fail');
};
