export type ReloadPreviewState = boolean;

const reloadPreviewReducer = (state: ReloadPreviewState = false, action: {type: string}) => {
  switch (action.type) {
    case 'ASSET_EDITION_RELOAD_PREVIEW_START':
      return true;
    case 'ASSET_EDITION_RELOAD_PREVIEW_STOP':
      return false;
    default:
      break;
  }

  return state;
};

export default reloadPreviewReducer;
