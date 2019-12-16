import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {
  addLines,
  removeLine,
  assetCreationSucceeded,
  assetCreationFailed,
  updateLine,
} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {
  OnAddLineAction,
  OnFileThumbnailGenerationAction,
  OnRemoveLineAction,
  OnFileUploadSuccessAction,
  OnFileUploadProgressAction,
  OnLineCreationStartAction,
  OnAssetCreationSuccessAction,
  OnAssetCreationFailAction,
  OnEditLineAction,
  OnRemoveAllLinesAction,
  ADD_LINES,
  REMOVE_LINE,
  REMOVE_ALL_LINES,
  FILE_THUMBNAIL_GENERATION,
  EDIT_LINE,
  FILE_UPLOAD_SUCCESS,
  FILE_UPLOAD_PROGRESS,
  LINE_CREATION_START,
  ASSET_CREATION_SUCCESS,
  ASSET_CREATION_FAIL,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';

export type State = {
  lines: Line[];
};

export const reducer = (
  state: State,
  action:
    | OnAddLineAction
    | OnFileThumbnailGenerationAction
    | OnRemoveLineAction
    | OnFileUploadSuccessAction
    | OnFileUploadProgressAction
    | OnLineCreationStartAction
    | OnAssetCreationSuccessAction
    | OnAssetCreationFailAction
    | OnEditLineAction
    | OnRemoveAllLinesAction
) => {
  switch (action.type) {
    case ADD_LINES:
      return {...state, lines: addLines(state.lines, action.payload.lines)};
    case REMOVE_LINE:
      return {...state, lines: removeLine(state.lines, action.payload.line)};
    case REMOVE_ALL_LINES:
      return {...state, lines: []};
    case FILE_THUMBNAIL_GENERATION:
      return {...state, lines: updateLine(state.lines, action.payload.line.id, {thumbnail: action.payload.thumbnail})};
    case EDIT_LINE:
      return {
        ...state,
        lines: updateLine(state.lines, action.payload.line.id, {
          code: action.payload.line.code,
          channel: action.payload.line.channel,
          locale: action.payload.line.locale,
        }),
      };
    case FILE_UPLOAD_SUCCESS:
      return {
        ...state,
        lines: updateLine(state.lines, action.payload.line.id, {file: action.payload.file, isSending: false}),
      };
    case FILE_UPLOAD_PROGRESS:
      return {
        ...state,
        lines: updateLine(state.lines, action.payload.line.id, {
          uploadProgress: action.payload.progress,
          isSending: true,
        }),
      };
    case LINE_CREATION_START:
      return {...state, lines: updateLine(state.lines, action.payload.line.id, {isAssetCreating: true})};
    case ASSET_CREATION_SUCCESS:
      return {...state, lines: assetCreationSucceeded(state.lines, action.payload.asset)};
    case ASSET_CREATION_FAIL:
      return {...state, lines: assetCreationFailed(state.lines, action.payload.asset, action.payload.errors)};
    default:
      return state;
  }
};
