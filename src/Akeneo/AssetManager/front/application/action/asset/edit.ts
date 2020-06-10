import {
  assetEditionReceived,
  assetEditionErrorOccured,
  assetEditionSucceeded,
  assetEditionValueUpdated,
  assetEditionUpdated,
  assetEditionSubmission,
} from 'akeneoassetmanager/domain/event/asset/edit';
import {
  notifyAssetWellSaved,
  notifyAssetSaveFailed,
  notifyAssetSaveValidationError,
} from 'akeneoassetmanager/application/action/asset/notify';
import assetSaver from 'akeneoassetmanager/infrastructure/saver/asset';
import assetFetcher, {AssetResult} from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';

export const saveAsset = () => async (dispatch: any, getState: () => EditState): Promise<boolean> => {
  const asset = getState().form.data;

  dispatch(assetEditionSubmission());
  try {
    const errors = await assetSaver.save(asset);
    if (errors) {
      if (Array.isArray(errors)) {
        dispatch(assetEditionErrorOccured(errors));
      } else {
        console.error(errors);
      }
      dispatch(notifyAssetSaveValidationError());

      return false;
    }
  } catch (error) {
    dispatch(notifyAssetSaveFailed());

    return false;
  }

  dispatch(assetEditionSucceeded());
  dispatch(notifyAssetWellSaved());
  const savedAsset: AssetResult = await assetFetcher.fetch(asset.assetFamily.identifier, asset.code);

  dispatch(assetEditionReceived(savedAsset.asset));

  return true;
};

export const assetValueUpdated = (value: EditionValue) => (dispatch: any, getState: any) => {
  dispatch(assetEditionValueUpdated(value));
  dispatch(assetEditionUpdated(getState().form.data));
};
