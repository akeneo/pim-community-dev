import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';

export const enrichedEntityReceived = (enrichedEntity: EnrichedEntity) => {
  return {type: 'ENRICHED_ENTITY_RECEIVED', enrichedEntity};
};
