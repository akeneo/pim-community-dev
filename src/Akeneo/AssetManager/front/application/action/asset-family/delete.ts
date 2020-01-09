import {
  notifyAssetFamilyWellDeleted,
  notifyAssetFamilyDeleteFailed,
  notifyAssetFamilyDeletionErrorOccured,
} from 'akeneoassetmanager/application/action/asset-family/notify';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import assetFamilyRemover from 'akeneoassetmanager/infrastructure/remover/asset-family';
import {redirectToAssetFamilyListItem} from 'akeneoassetmanager/application/action/asset-family/router';

export const deleteAssetFamily = (assetFamily: AssetFamily) => async (dispatch: any): Promise<void> => {
  try {
    const errors = await assetFamilyRemover.remove(assetFamily.identifier);

    if (errors) {
      dispatch(notifyAssetFamilyDeletionErrorOccured(errors));

      return;
    }

    dispatch(notifyAssetFamilyWellDeleted());
    dispatch(redirectToAssetFamilyListItem());
  } catch (error) {
    dispatch(notifyAssetFamilyDeleteFailed());

    throw error;
  }
};
