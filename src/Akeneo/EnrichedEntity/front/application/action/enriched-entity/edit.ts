import {enrichedEntityUpdated, enrichedEntityReceived} from 'akeneoenrichedentity/domain/event/enriched-entity/edit';
import {postSave, failSave} from 'akeneoenrichedentity/application/event/form-state';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';
import enrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';

export const saveEnrichedEntity = (enrichedEntity: EnrichedEntity) => async (dispatch: any): Promise<void> => {
  try {
    await enrichedEntitySaver.save(enrichedEntity);

    dispatch(postSave());

    const savedEnrichedEntity: EnrichedEntity = await enrichedEntityFetcher.fetch(
      enrichedEntity.getIdentifier().stringValue()
    );

    dispatch(enrichedEntityReceived(savedEnrichedEntity));
  } catch (error) {
    dispatch(failSave(error));
  }
};

export const updateEnrichedEntity = (enrichedEntity: EnrichedEntity) => {
  return enrichedEntityUpdated(enrichedEntity);
};
