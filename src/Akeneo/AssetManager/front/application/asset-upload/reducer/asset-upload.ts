import Line from 'akeneoassetmanager/application/asset-upload/model/line';

export interface AssetUploadState {
  lines: Line[];
}

const PREPEND_LINES = 'asset-upload/PREPEND_LINES';

export default (state: AssetUploadState, action: any): AssetUploadState => {
  switch (action.type) {
    case PREPEND_LINES:
      return {
        ...state,
        lines: [
          action.payload,
          ...state.lines,
        ],
      };
    default:
      return state;
  }
}

export const prependLines = (lines: Line[]) => {
  return {
    type: PREPEND_LINES,
    payload: lines,
  };
};
