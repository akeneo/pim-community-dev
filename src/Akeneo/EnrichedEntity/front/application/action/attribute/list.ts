import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {denormalizeEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import attributeFetcher from 'akeneoenrichedentity/infrastructure/fetcher/attribute';
import attributeRemover from 'akeneoenrichedentity/infrastructure/remover/attribute';
import {attributeListUpdated} from 'akeneoenrichedentity/domain/event/attribute/list';
import {
  notifyAttributeListUpdateFailed,
  notifyAttributeWellDeleted,
  notifyAttributeDeletionFailed,
} from 'akeneoenrichedentity/application/action/attribute/notify';
import {attributeDeleted} from 'akeneoenrichedentity/domain/event/attribute/list';
import {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import AttributeIdentifier from 'akeneoenrichedentity/domain/model/attribute/identifier';
import {attributeEditionCancel} from 'akeneoenrichedentity/domain/event/attribute/edit';

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

export const deleteAttribute = (attributeIdentifier: AttributeIdentifier) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  try {
    const enrichedEntityIdentifier = createIdentifier(getState().form.data.identifier);
    const errors = await attributeRemover.remove(enrichedEntityIdentifier, attributeIdentifier);

    if (errors) {
      dispatch(notifyAttributeDeletionFailed());
      return;
    }

    dispatch(attributeDeleted(attributeIdentifier));
    dispatch(notifyAttributeWellDeleted());
    dispatch(updateAttributeList());
  } catch (error) {
    dispatch(notifyAttributeDeletionFailed());

    throw error;
  }

  dispatch(attributeEditionCancel());
};
