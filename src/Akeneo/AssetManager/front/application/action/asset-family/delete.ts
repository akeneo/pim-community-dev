import {
  notifyAssetFamilyWellDeleted,
  notifyAssetFamilyDeleteFailed,
  notifyAssetFamilyDeletionErrorOccured,
} from 'akeneoassetmanager/application/action/asset-family/notify';
import AssetFamily from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import assetFamilyRemover from 'akeneoassetmanager/infrastructure/remover/asset-family';
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {redirectToAssetFamilyListItem} from 'akeneoassetmanager/application/action/asset-family/router';
import {closeDeleteModal} from 'akeneoassetmanager/application/event/confirmDelete';

export const deleteAssetFamily = (assetFamily: AssetFamily) => async (dispatch: any): Promise<void> => {
  try {
    const errors = await assetFamilyRemover.remove(assetFamily.getIdentifier());

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(notifyAssetFamilyDeletionErrorOccured(validationErrors));

      return;
    }

    dispatch(notifyAssetFamilyWellDeleted());
    dispatch(redirectToAssetFamilyListItem());
    dispatch(closeDeleteModal());
  } catch (error) {
    dispatch(notifyAssetFamilyDeleteFailed());

    throw error;
  }
};
