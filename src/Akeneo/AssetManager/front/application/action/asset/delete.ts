import {
  notifyAssetWellDeleted,
  notifyAssetDeleteFailed,
  notifyAssetDeletionErrorOccured,
} from 'akeneoassetmanager/application/action/asset/notify';
import assetRemover from 'akeneoassetmanager/infrastructure/remover/asset';
import {redirectToAssetIndex} from 'akeneoassetmanager/application/action/asset/router';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
export const deleteAsset = (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => async (
  dispatch: any
): Promise<void> => {
  try {
    const errors = await assetRemover.remove(assetFamilyIdentifier, assetCode);
    if (errors) {
      dispatch(notifyAssetDeletionErrorOccured(errors));

      return;
    }

    dispatch(notifyAssetWellDeleted(assetCode));
    dispatch(redirectToAssetIndex());
  } catch (error) {
    dispatch(notifyAssetDeleteFailed());

    throw error;
  }
};
