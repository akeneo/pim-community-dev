import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {denormalizeEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import attributeFetcher from 'akeneoenrichedentity/infrastructure/fetcher/attribute';
import {attributeListUpdated} from 'akeneoenrichedentity/domain/event/attribute/list';
import {notifyAttributeListUpdateFailed} from 'akeneoenrichedentity/application/action/attribute/notify';

export const updateAttributeList = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const enrichedEntity = denormalizeEnrichedEntity(getState().form.data);
  try {
    const attributes = await attributeFetcher.fetchAll(enrichedEntity.getIdentifier());
    dispatch(attributeListUpdated(attributes));
  } catch (error) {
    dispatch(notifyAttributeListUpdateFailed());

    throw error;
  }
};
