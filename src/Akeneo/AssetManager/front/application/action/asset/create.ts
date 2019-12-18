import {notifyAssetCreateFailed, notifyAssetWellCreated} from 'akeneoassetmanager/application/action/asset/notify';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
  assetCreationErrorOccured,
  assetCreationSucceeded,
  assetCreationStart,
} from 'akeneoassetmanager/domain/event/asset/create';
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import assetSaver from 'akeneoassetmanager/infrastructure/saver/asset';
import {redirectToAsset} from 'akeneoassetmanager/application/action/asset/router';
import {updateAssetResults} from 'akeneoassetmanager/application/action/asset/search';

export const createAsset = (createAnother: boolean) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const assetFamily = getState().form.data;
  const {code, labels} = getState().createAsset.data;
  const asset = {
    code,
    assetFamilyIdentifier: assetFamily.identifier,
    labels,
    values: [],
  };

  try {
    let errors = await assetSaver.create(asset);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(assetCreationErrorOccured(validationErrors));

      return;
    }
  } catch (error) {
    dispatch(notifyAssetCreateFailed());

    return;
  }

  dispatch(notifyAssetWellCreated());
  if (createAnother) {
    dispatch(updateAssetResults());
    dispatch(assetCreationStart());
  } else {
    dispatch(assetCreationSucceeded());
    dispatch(redirectToAsset(asset.assetFamilyIdentifier, asset.code));
  }

  return;
};
