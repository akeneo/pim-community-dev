import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import attributeRemover from 'akeneoassetmanager/infrastructure/remover/attribute';
import {
  notifyAttributeWellDeleted,
  notifyAttributeDeletionFailed,
} from 'akeneoassetmanager/application/action/attribute/notify';
import {attributeDeleted} from 'akeneoassetmanager/domain/event/attribute/list';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {attributeEditionCancel} from 'akeneoassetmanager/domain/event/attribute/edit';
import {updateAttributeList} from 'akeneoassetmanager/application/action/attribute/list';
import {closeDeleteModal} from 'akeneoassetmanager/application/event/confirmDelete';

export const deleteAttribute = (attributeIdentifier: AttributeIdentifier) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  dispatch(attributeEditionCancel());
  dispatch(attributeDeleted(attributeIdentifier));
  try {
    const assetFamilyIdentifier = getState().form.data.identifier;
    const errors = await attributeRemover.remove(assetFamilyIdentifier, attributeIdentifier);

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
