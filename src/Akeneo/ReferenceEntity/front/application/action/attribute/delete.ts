import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import attributeRemover from 'akeneoreferenceentity/infrastructure/remover/attribute';
import {
  notifyAttributeWellDeleted,
  notifyAttributeDeletionFailed,
} from 'akeneoreferenceentity/application/action/attribute/notify';
import {attributeDeleted} from 'akeneoreferenceentity/domain/event/attribute/list';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import AttributeIdentifier from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {attributeEditionCancel} from 'akeneoreferenceentity/domain/event/attribute/edit';
import {updateAttributeList} from 'akeneoreferenceentity/application/action/attribute/list';
import {closeDeleteModal} from 'akeneoreferenceentity/application/event/confirmDelete';

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
    dispatch(closeDeleteModal());
  } catch (error) {
    dispatch(notifyAttributeDeletionFailed());

    throw error;
  } finally {
    dispatch(updateAttributeList());
  }
};
