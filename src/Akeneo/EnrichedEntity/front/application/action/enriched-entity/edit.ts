import {enrichedEntitySaved, enrichedEntityUpdated} from 'akeneoenrichedentity/domain/event/enriched-entity/edit';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';

export const saveEnrichedEntity = (enrichedEntity: EnrichedEntity) => async (dispatch: any): Promise<void> => {
  const savedEnrichedEntity: EnrichedEntity = await enrichedEntitySaver.save(enrichedEntity);

  dispatch(enrichedEntitySaved(savedEnrichedEntity));
};

export const updateEnrichedEntity = (enrichedEntity: EnrichedEntity) => {
  return enrichedEntityUpdated(enrichedEntity);
};
