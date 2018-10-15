import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {denormalizeReferenceEntity} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import attributeFetcher from 'akeneoreferenceentity/infrastructure/fetcher/attribute';
import {attributeListUpdated} from 'akeneoreferenceentity/domain/event/attribute/list';
import {updateColumns} from 'akeneoreferenceentity/application/event/search';
import {getColumns} from 'akeneoreferenceentity/application/configuration/value';
import {notifyAttributeListUpdateFailed} from 'akeneoreferenceentity/application/action/attribute/notify';

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
