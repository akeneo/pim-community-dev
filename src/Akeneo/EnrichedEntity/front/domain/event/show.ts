import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';

export const enrichedEntityReceived = (enrichedEntity: EnrichedEntity) => {
  return {type: 'ENRICHED_ENTITY_RECEIVED', enrichedEntity};
};

export const enrichedEntityUpdated = (enrichedEntity: EnrichedEntity) => {
  return {type: 'ENRICHED_ENTITY_UPDATED', enrichedEntity};
};
