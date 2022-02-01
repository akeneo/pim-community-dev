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
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {denormalizePermissionCollection} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {refreshAssetFamily} from 'akeneoassetmanager/application/action/asset-family/edit';
import {AssetFamilyFetcher} from 'akeneoassetmanager/domain/fetcher/asset-family';

export const savePermission = (assetFamilyFetcher: AssetFamilyFetcher) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const assetFamilyIdentifier = getState().form.data.identifier;
  const permission = denormalizePermissionCollection(getState().permission.data);

  try {
    const errors = await permissionSaver.save(assetFamilyIdentifier, permission);
    if (errors) {
      dispatch(permissionEditionErrorOccured(errors));
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
  dispatch(refreshAssetFamily(assetFamilyFetcher, assetFamilyIdentifier, false));
};
