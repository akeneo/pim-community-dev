import {enrichedEntityUpdated} from 'akeneoenrichedentity/domain/event/show';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import EnrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';

export const saveEditForm = (enrichedEntity: EnrichedEntity) => async (dispatch: any) => {
  const enrichedEntitySaved: EnrichedEntity = await EnrichedEntityFetcher.save(enrichedEntity);

  dispatch(enrichedEntityUpdated(enrichedEntitySaved));
};
