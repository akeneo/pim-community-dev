import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {
  addLines,
  assetCreationFailed,
  assetCreationSucceeded,
  removeLine,
  updateLine,
} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {
  ADD_LINES,
  ASSET_CREATION_FAIL,
  ASSET_CREATION_SUCCESS,
  EDIT_LINE,
  FILE_THUMBNAIL_GENERATION_DONE,
  FILE_UPLOAD_PROGRESS,
  FILE_UPLOAD_SUCCESS,
  LINE_CREATION_START,
  REMOVE_ALL_LINES,
  REMOVE_LINE,
  OnAddLineAction,
  OnAssetCreationFailAction,
  OnAssetCreationSuccessAction,
  OnEditLineAction,
  OnFileThumbnailGenerationDoneAction,
  OnFileUploadProgressAction,
  OnFileUploadSuccessAction,
  OnLineCreationStartAction,
  OnRemoveAllLinesAction,
  OnRemoveLineAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';

export type State = {
  lines: Line[];
};

export const reducer = (
  state: State,
  action:
    | OnAddLineAction
    | OnFileThumbnailGenerationDoneAction
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
    case FILE_THUMBNAIL_GENERATION_DONE:
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
        lines: updateLine(state.lines, action.payload.line.id, {file: action.payload.file, isFileUploading: false}),
      };
    case FILE_UPLOAD_PROGRESS:
      return {
        ...state,
        lines: updateLine(state.lines, action.payload.line.id, {
          uploadProgress: action.payload.progress,
          isFileUploading: true,
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
