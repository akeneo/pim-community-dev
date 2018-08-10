import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {denormalizeEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Attribute from 'akeneoenrichedentity/domain/model/attribute/attribute';
import attributeFetcher from 'akeneoenrichedentity/infrastructure/fetcher/attribute';
import attributeRemover from 'akeneoenrichedentity/infrastructure/remover/attribute';
import {attributeListUpdated} from 'akeneoenrichedentity/domain/event/attribute/list';
import {notifyAttributeListUpdateFailed, notifyAttributeWellDeleted, notifyAttributeDeletionFailed} from 'akeneoenrichedentity/application/action/attribute/notify';
import {attributeDeleted} from 'akeneoenrichedentity/domain/event/attribute/list'

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

export const deleteAttribute = (attribute: Attribute) => async (dispatch: any): Promise<void> => {
  try {
    await attributeRemover.remove(attribute.getIdentifier());
    dispatch(attributeDeleted(attribute));
    dispatch(notifyAttributeWellDeleted());
    dispatch(updateAttributeList());
  } catch (error) {
    dispatch(notifyAttributeDeletionFailed());

    throw error;
  }
};
