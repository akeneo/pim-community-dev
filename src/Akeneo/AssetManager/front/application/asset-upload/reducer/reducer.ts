import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {
  addLines,
  assetCreationFailed,
  assetCreationSucceeded,
  assetUploadFailed,
  removeLine,
  sortLinesWithValidationErrorsFirst,
  updateLine,
} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {
  ADD_LINES,
  ASSET_CREATION_FAIL,
  ASSET_CREATION_SUCCESS,
  EDIT_LINE,
  FILE_THUMBNAIL_GENERATION_DONE,
  FILE_UPLOAD_FAILURE,
  FILE_UPLOAD_PROGRESS,
  FILE_UPLOAD_START,
  FILE_UPLOAD_SUCCESS,
  LINE_CREATION_START,
  REMOVE_ALL_LINES,
  REMOVE_LINE,
  OnAddLineAction,
  OnAssetCreationFailAction,
  OnAssetCreationSuccessAction,
  OnEditLineAction,
  OnFileThumbnailGenerationDoneAction,
  OnFileUploadFailureAction,
  OnFileUploadProgressAction,
  OnFileUploadStartAction,
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
    | OnAssetCreationFailAction
    | OnAssetCreationSuccessAction
    | OnEditLineAction
    | OnFileThumbnailGenerationDoneAction
    | OnFileUploadFailureAction
    | OnFileUploadProgressAction
    | OnFileUploadStartAction
    | OnFileUploadSuccessAction
    | OnLineCreationStartAction
    | OnRemoveAllLinesAction
    | OnRemoveLineAction
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
    case FILE_UPLOAD_START:
      return {
        ...state,
        lines: updateLine(state.lines, action.payload.line.id, {
          isFileUploading: true,
          isFileUploadFailed: false,
          uploadProgress: 0,
          errors: {
            back: [],
            front: [],
          },
        }),
      };
    case FILE_UPLOAD_SUCCESS:
      return {
        ...state,
        lines: updateLine(state.lines, action.payload.line.id, {
          file: action.payload.file,
          isFileUploading: false,
          isFileUploadFailed: false,
        }),
      };
    case FILE_UPLOAD_FAILURE:
      return {
        ...state,
        lines: sortLinesWithValidationErrorsFirst(
          assetUploadFailed(state.lines, action.payload.line, action.payload.errors)
        ),
      };
    case FILE_UPLOAD_PROGRESS:
      return {
        ...state,
        lines: updateLine(state.lines, action.payload.line.id, {
          uploadProgress: action.payload.progress,
          isFileUploading: true,
          isFileUploadFailed: false,
        }),
      };
    case LINE_CREATION_START:
      return {
        ...state,
        lines: updateLine(state.lines, action.payload.line.id, {
          isAssetCreating: true,
          errors: {
            back: [],
            front: [],
          },
        }),
      };
    case ASSET_CREATION_SUCCESS:
      return {
        ...state,
        lines: sortLinesWithValidationErrorsFirst(assetCreationSucceeded(state.lines, action.payload.asset)),
      };
    case ASSET_CREATION_FAIL:
      return {
        ...state,
        lines: sortLinesWithValidationErrorsFirst(
          assetCreationFailed(state.lines, action.payload.asset, action.payload.errors)
        ),
      };
    default:
      return state;
  }
};
