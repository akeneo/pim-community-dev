import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import namingConventionExecutor from 'akeneoassetmanager/infrastructure/naming-convention-executor';
import {
  notifyExecuteNamingConventionFailed,
  notifyExecuteNamingConventionSuccess,
} from 'akeneoassetmanager/application/action/asset/notify';
import {saveAsset} from 'akeneoassetmanager/application/action/asset/edit';
import assetFetcher, {AssetResult} from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {
  assetEditionErrorOccured,
  assetEditionReceived,
  assetEditionSubmission,
} from 'akeneoassetmanager/domain/event/asset/edit';

async function fetchUpdatedAsset(assetFamilyIdentifier: string, assetCode: string, dispatch: any) {
  const savedAsset: AssetResult = await assetFetcher.fetch(assetFamilyIdentifier, assetCode);
  dispatch(assetEditionReceived(savedAsset.asset));
}

export const saveAndExecuteNamingConvention = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  assetCode: AssetCode
) => async (dispatch: any): Promise<void> => {
  const isSaved = await dispatch(saveAsset());
  if (!isSaved) {
    dispatch(notifyExecuteNamingConventionFailed());

    return;
  }
  const isExecuted = await executeNamingConvention(assetFamilyIdentifier, assetCode, dispatch);
  if (!isExecuted) {
    return;
  }
  await fetchUpdatedAsset(assetFamilyIdentifier, assetCode, dispatch);
};

async function executeNamingConvention(
  assetFamilyIdentifier: string,
  assetCode: string,
  dispatch: any
): Promise<boolean> {
  try {
    dispatch(assetEditionSubmission());

    const errors = await namingConventionExecutor.execute(assetFamilyIdentifier, assetCode);
    if (errors) {
      if (Array.isArray(errors)) {
        dispatch(assetEditionErrorOccured(errors));
      } else {
        console.error(errors);
      }
      dispatch(notifyExecuteNamingConventionFailed());

      return false;
    }
  } catch (error) {
    console.error(error);
    dispatch(notifyExecuteNamingConventionFailed());

    return false;
  }

  dispatch(notifyExecuteNamingConventionSuccess());

  return true;
}
