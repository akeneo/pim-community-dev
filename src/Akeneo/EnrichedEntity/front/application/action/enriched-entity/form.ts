import {enrichedEntityUpdated} from 'akeneoenrichedentity/domain/event/show';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';

export const saveEditForm = (enrichedEntity: EnrichedEntity) => async (dispatch: any): Promise<void> => {
  const enrichedEntitySaved: EnrichedEntity = await enrichedEntitySaver.save(enrichedEntity);

  dispatch(enrichedEntityUpdated(enrichedEntitySaved));
};
