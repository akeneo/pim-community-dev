import assetFamilySaver from 'akeneoassetmanager/infrastructure/saver/asset-family';
import {
  assetFamilyCreationSucceeded,
  assetFamilyCreationErrorOccured,
} from 'akeneoassetmanager/domain/event/asset-family/create';
import {
  notifyAssetFamilyWellCreated,
  notifyAssetFamilyCreateFailed,
} from 'akeneoassetmanager/application/action/asset-family/notify';
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {IndexState} from 'akeneoassetmanager/application/reducer/asset-family/index';
import {redirectToAssetFamily} from 'akeneoassetmanager/application/action/asset-family/router';
import {createAssetFamilyCreationFromNormalized} from 'akeneoassetmanager/domain/model/asset-family/creation';

export const createAssetFamily = () => async (dispatch: any, getState: () => IndexState): Promise<void> => {
  const assetFamily = createAssetFamilyCreationFromNormalized(getState().create.data);

  try {
    let errors = await assetFamilySaver.create(assetFamily);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(assetFamilyCreationErrorOccured(validationErrors));

      return;
    }
  } catch (error) {
    dispatch(notifyAssetFamilyCreateFailed());

    return;
  }

  dispatch(assetFamilyCreationSucceeded());
  dispatch(notifyAssetFamilyWellCreated());
  dispatch(redirectToAssetFamily(assetFamily.code, 'attribute'));

  return;
};
