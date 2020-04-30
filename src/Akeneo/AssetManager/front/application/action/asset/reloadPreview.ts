import {
  assetEditionReloadPreviewStart,
  assetEditionReloadPreviewStop,
} from 'akeneoassetmanager/domain/event/asset/reloadPreview';

export const doReloadAllPreviews = () => (dispatch: any) => {
  dispatch(assetEditionReloadPreviewStart());

  setTimeout(() => {
    dispatch(assetEditionReloadPreviewStop());
  }, 500);
};
