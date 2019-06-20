import {
  assetEditionLabelUpdated,
  assetEditionReceived,
  assetEditionImageUpdated,
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
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import File from 'akeneoassetmanager/domain/model/file';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {redirectToAssetIndex} from 'akeneoassetmanager/application/action/asset/router';
import denormalizeAsset from 'akeneoassetmanager/application/denormalizer/asset';
import Value from 'akeneoassetmanager/domain/model/asset/value';

export const saveAsset = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const asset = denormalizeAsset(getState().form.data);

  dispatch(assetEditionSubmission());
  try {
    const errors = await assetSaver.save(asset);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(assetEditionErrorOccured(validationErrors));
      dispatch(notifyAssetSaveValidationError());

      return;
    }
  } catch (error) {
    dispatch(notifyAssetSaveFailed());

    return;
  }

  dispatch(assetEditionSucceeded());
  dispatch(notifyAssetWellSaved());
  const savedAsset: AssetResult = await assetFetcher.fetch(asset.getAssetFamilyIdentifier(), asset.getCode());

  dispatch(assetEditionReceived(savedAsset.asset));
};

export const assetLabelUpdated = (value: string, locale: string) => (dispatch: any, getState: any) => {
  dispatch(assetEditionLabelUpdated(value, locale));
  dispatch(assetEditionUpdated(getState().form.data));
};

export const assetImageUpdated = (image: File) => (dispatch: any, getState: any) => {
  dispatch(assetEditionImageUpdated(image));
  dispatch(assetEditionUpdated(getState().form.data));
};

export const assetValueUpdated = (value: Value) => (dispatch: any, getState: any) => {
  dispatch(assetEditionValueUpdated(value));
  dispatch(assetEditionUpdated(getState().form.data));
};

export const backToAssetFamily = () => (dispatch: any, getState: any) => {
  const asset = denormalizeAsset(getState().form.data);
  dispatch(redirectToAssetIndex(asset.getAssetFamilyIdentifier()));
};
