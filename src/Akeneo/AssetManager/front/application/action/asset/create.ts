import {notifyAssetCreateFailed, notifyAssetWellCreated} from 'akeneoassetmanager/application/action/asset/notify';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
  assetCreationErrorOccured,
  assetCreationSucceeded,
  assetCreationStart,
} from 'akeneoassetmanager/domain/event/asset/create';
import {createAsset as assetFactory} from 'akeneoassetmanager/domain/model/asset/asset';
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import assetSaver from 'akeneoassetmanager/infrastructure/saver/asset';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import {createValueCollection} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {redirectToAsset} from 'akeneoassetmanager/application/action/asset/router';
import {updateAssetResults} from 'akeneoassetmanager/application/action/asset/search';
import {denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';

export const createAsset = (createAnother: boolean) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const assetFamily = getState().form.data;
  const {code, labels} = getState().createAsset.data;
  const asset = assetFactory(
    code,
    assetFamily.identifier,
    denormalizeAssetCode(code),
    labels,
    createEmptyFile(),
    createValueCollection([])
  );

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
    dispatch(redirectToAsset(asset.getAssetFamilyIdentifier(), asset.getCode()));
  }

  return;
};
