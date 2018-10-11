import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {denormalizeReferenceEntity} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import attributeFetcher from 'akeneoreferenceentity/infrastructure/fetcher/attribute';
import attributeRemover from 'akeneoreferenceentity/infrastructure/remover/attribute';
import {attributeListUpdated} from 'akeneoreferenceentity/domain/event/attribute/list';
import {
  notifyAttributeListUpdateFailed,
  notifyAttributeWellDeleted,
  notifyAttributeDeletionFailed,
} from 'akeneoreferenceentity/application/action/attribute/notify';
import {attributeDeleted} from 'akeneoreferenceentity/domain/event/attribute/list';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import AttributeIdentifier from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {attributeEditionCancel} from 'akeneoreferenceentity/domain/event/attribute/edit';
import {updateColumns} from 'akeneoreferenceentity/application/event/search';
import {getColumns} from 'akeneoreferenceentity/application/configuration/value';

export const updateAttributeList = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const referenceEntity = denormalizeReferenceEntity(getState().form.data);
  try {
    const attributes = await attributeFetcher.fetchAll(referenceEntity.getIdentifier());
    dispatch(attributeListUpdated(attributes));
    dispatch(updateColumns(getColumns(attributes, getState().structure.channels)));
  } catch (error) {
    dispatch(notifyAttributeListUpdateFailed());

    throw error;
  }
};

export const deleteAttribute = (attributeIdentifier: AttributeIdentifier) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  dispatch(attributeEditionCancel());
  dispatch(attributeDeleted(attributeIdentifier));
  try {
    const referenceEntityIdentifier = createIdentifier(getState().form.data.identifier);
    const errors = await attributeRemover.remove(referenceEntityIdentifier, attributeIdentifier);

    if (errors) {
      dispatch(notifyAttributeDeletionFailed());
      return;
    }

    dispatch(notifyAttributeWellDeleted());
  } catch (error) {
    dispatch(notifyAttributeDeletionFailed());

    throw error;
  } finally {
    dispatch(updateAttributeList());
  }
};
