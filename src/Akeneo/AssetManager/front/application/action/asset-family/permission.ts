import {
  permissionEditionErrorOccured,
  permissionEditionSucceeded,
  permissionEditionReceived,
} from 'akeneoassetmanager/domain/event/asset-family/permission';
import {
  notifyPermissionWellSaved,
  notifyPermissionSaveFailed,
} from 'akeneoassetmanager/application/action/asset-family/notify';
import permissionSaver from 'akeneoassetmanager/infrastructure/saver/permission';
import permissionFetcher from 'akeneoassetmanager/infrastructure/fetcher/permission';
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {denormalizePermissionCollection} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {refreshAssetFamily} from 'akeneoassetmanager/application/action/asset-family/edit';

export const savePermission = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const assetFamilyIdentifier = getState().form.data.identifier;
  const permission = denormalizePermissionCollection(getState().permission.data);

  try {
    const errors = await permissionSaver.save(assetFamilyIdentifier, permission);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(permissionEditionErrorOccured(validationErrors));
      dispatch(notifyPermissionSaveFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyPermissionSaveFailed());

    return;
  }

  dispatch(permissionEditionSucceeded());
  dispatch(notifyPermissionWellSaved());

  const updatedPermission = await permissionFetcher.fetch(assetFamilyIdentifier);
  dispatch(permissionEditionReceived(updatedPermission));
  dispatch(refreshAssetFamily(assetFamilyIdentifier, false));
};
