import {
  assetFamilyEditionLabelUpdated,
  assetFamilyEditionReceived,
  assetFamilyEditionUpdated,
  assetFamilyEditionImageUpdated,
  assetFamilyEditionErrorOccured,
  assetFamilyEditionSucceeded,
  assetFamilyAssetCountUpdated,
} from 'akeneoassetmanager/domain/event/asset-family/edit';
import {
  notifyAssetFamilyWellSaved,
  notifyAssetFamilySaveFailed,
} from 'akeneoassetmanager/application/action/asset-family/notify';
import {denormalizeAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import assetFamilySaver from 'akeneoassetmanager/infrastructure/saver/asset-family';
import assetFamilyFetcher, {
  AssetFamilyResult,
} from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import File from 'akeneoassetmanager/domain/model/file';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {assetFamilyPermissionChanged} from 'akeneoassetmanager/domain/event/user';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';

export const saveAssetFamily = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const assetFamily = denormalizeAssetFamily(getState().form.data);

  try {
    const errors = await assetFamilySaver.save(assetFamily);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(assetFamilyEditionErrorOccured(validationErrors));
      dispatch(notifyAssetFamilySaveFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyAssetFamilySaveFailed());

    return;
  }

  dispatch(assetFamilyEditionSucceeded());
  dispatch(notifyAssetFamilyWellSaved());

  dispatch(refreshAssetFamily(assetFamily.getIdentifier()));
};

export const refreshAssetFamily = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  refreshDataForm: boolean = false
) => async (dispatch: any): Promise<void> => {
  const assetFamilyResult: AssetFamilyResult = await assetFamilyFetcher.fetch(assetFamilyIdentifier);
  if (refreshDataForm) {
    dispatch(assetFamilyAssetCountUpdated(assetFamilyResult.assetCount));
  }
  dispatch(assetFamilyEditionReceived(assetFamilyResult.assetFamily.normalize()));
  dispatch(assetFamilyPermissionChanged(assetFamilyResult.permission));
};

export const assetFamilyLabelUpdated = (value: string, locale: string) => (
  dispatch: any,
  getState: () => EditState
) => {
  dispatch(assetFamilyEditionLabelUpdated(value, locale));
  dispatch(assetFamilyEditionUpdated(getState().form.data));
};

export const assetFamilyImageUpdated = (image: File) => (dispatch: any, getState: () => EditState) => {
  dispatch(assetFamilyEditionImageUpdated(image));
  dispatch(assetFamilyEditionUpdated(getState().form.data));
};
