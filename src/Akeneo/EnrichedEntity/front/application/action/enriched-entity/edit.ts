import {enrichedEntitySaved, enrichedEntityUpdated} from 'akeneoenrichedentity/domain/event/enriched-entity/edit';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';
import enrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';

export const saveEnrichedEntity = (enrichedEntity: EnrichedEntity) => async (dispatch: any): Promise<void> => {
  await enrichedEntitySaver.save(enrichedEntity);
  const savedEnrichedEntity: EnrichedEntity = await enrichedEntityFetcher.fetch(enrichedEntity.getIdentifier().stringValue());

  dispatch(enrichedEntitySaved(savedEnrichedEntity));
};

export const updateEnrichedEntity = (enrichedEntity: EnrichedEntity) => {
  return enrichedEntityUpdated(enrichedEntity);
};
