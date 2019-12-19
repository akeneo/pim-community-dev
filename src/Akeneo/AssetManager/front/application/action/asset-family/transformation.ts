import computeTransformationsLauncher from 'akeneoassetmanager/infrastructure/compute-transformations-launcher';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
  notifyLaunchComputeTransformationsFailed,
  notifyLaunchComputeTransformationsSucceeded
} from "akeneoassetmanager/application/action/asset-family/notify";

export const launchComputeTransformations = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const assetFamily = getState().form.data;

  try {
    const errors = await computeTransformationsLauncher.launch(assetFamily);

    if (errors) {
      dispatch(notifyLaunchComputeTransformationsFailed());
      return;
    }
  } catch (error) {
    dispatch(notifyLaunchComputeTransformationsFailed());

    return;
  }

  dispatch(notifyLaunchComputeTransformationsSucceeded());
};
