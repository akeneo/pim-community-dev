import {
  notifyAssetWellDeleted,
  notifyAssetDeleteFailed,
  notifyAssetDeletionErrorOccured,
  notifyAllAssetsWellDeleted,
  notifyAllAssetsDeletionFailed,
} from 'akeneoassetmanager/application/action/asset/notify';
import assetRemover from 'akeneoassetmanager/infrastructure/remover/asset';
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {updateAssetResults} from 'akeneoassetmanager/application/action/asset/search';
import {redirectToAssetIndex} from 'akeneoassetmanager/application/action/asset/router';
import AssetFamily from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {closeDeleteModal} from 'akeneoassetmanager/application/event/confirmDelete';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

export const deleteAsset = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  assetCode: AssetCode,
  updateAssetList: boolean = false
) => async (dispatch: any): Promise<void> => {
  try {
    const errors = await assetRemover.remove(assetFamilyIdentifier, assetCode);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(notifyAssetDeletionErrorOccured(validationErrors));

      return;
    }

    dispatch(notifyAssetWellDeleted(assetCode));
    dispatch(redirectToAssetIndex(assetFamilyIdentifier));
    dispatch(closeDeleteModal());
    if (true === updateAssetList) {
      dispatch(updateAssetResults());
    }
  } catch (error) {
    dispatch(notifyAssetDeleteFailed());

    throw error;
  }
};

export const deleteAllAssetFamilyAssets = (assetFamily: AssetFamily) => async (dispatch: any): Promise<void> => {
  try {
    const errors = await assetRemover.removeAll(assetFamily.getIdentifier());

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(notifyAssetDeletionErrorOccured(validationErrors));

      return;
    }

    dispatch(notifyAllAssetsWellDeleted(assetFamily.getIdentifier()));
    dispatch(updateAssetResults());
    dispatch(closeDeleteModal());
  } catch (error) {
    dispatch(notifyAllAssetsDeletionFailed());

    throw error;
  }
};
