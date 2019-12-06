import Line, {Thumbnail} from 'akeneoassetmanager/application/asset-upload/model/line';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {
  addLines,
  addThumbnail,
  removeLine,
  addUploadedFileToLine,
  updateUploadProgressToLine,
  lineCreationStart,
  assetCreationSucceeded,
  assetCreationFailed,
  editLine,
} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';
import {NormalizedValidationError as ValidationError} from 'akeneoassetmanager/domain/model/validation-error';

export const ADD_LINES = 'asset-upload/ADD_LINES';
export type OnAddLineAction = {
  type: typeof ADD_LINES;
  payload: {lines: Line[]};
};
export const linesAddedAction = (lines: Line[]): OnAddLineAction => ({
  type: ADD_LINES,
  payload: {
    lines,
  },
});

export const LINE_CREATION_START = 'asset-upload/LINE_CREATION_START';
export type OnLineCreationStartAction = {
  type: typeof LINE_CREATION_START;
  payload: {line: Line};
};
export const lineCreationStartAction = (line: Line): OnLineCreationStartAction => ({
  type: LINE_CREATION_START,
  payload: {
    line,
  },
});

export const ASSET_CREATION_SUCCESS = 'asset-upload/ASSET_CREATION_SUCCESS';
export type OnAssetCreationSuccessAction = {
  type: typeof ASSET_CREATION_SUCCESS;
  payload: {asset: CreationAsset};
};
export const assetCreationSuccessAction = (asset: CreationAsset): OnAssetCreationSuccessAction => ({
  type: ASSET_CREATION_SUCCESS,
  payload: {
    asset,
  },
});

export const ASSET_CREATION_FAIL = 'asset-upload/ASSET_CREATION_FAIL';
export type OnAssetCreationFailAction = {
  type: typeof ASSET_CREATION_FAIL;
  payload: {asset: CreationAsset; errors: ValidationError[]};
};
export const assetCreationFailAction = (
  asset: CreationAsset,
  errors: ValidationError[]
): OnAssetCreationFailAction => ({
  type: ASSET_CREATION_FAIL,
  payload: {
    asset,
    errors,
  },
});

export const REMOVE_LINE = 'asset-upload/REMOVE_LINE';
export type OnRemoveLineAction = {
  type: typeof REMOVE_LINE;
  payload: {line: Line};
};
export const removeLineAction = (line: Line): OnRemoveLineAction => ({
  type: REMOVE_LINE,
  payload: {
    line,
  },
});

export const REMOVE_ALL_LINES = 'asset-upload/REMOVE_ALL_LINES';
export type OnRemoveAllLinesAction = {
  type: typeof REMOVE_ALL_LINES;
  payload: {};
};
export const removeAllLinesAction = (): OnRemoveAllLinesAction => ({
  type: REMOVE_ALL_LINES,
  payload: {},
});

export const EDIT_LINE = 'asset-upload/EDIT_LINE';
export type OnEditLineAction = {
  type: typeof EDIT_LINE;
  payload: {line: Line};
};
export const editLineAction = (line: Line): OnEditLineAction => ({
  type: EDIT_LINE,
  payload: {
    line,
  },
});

export const FILE_UPLOAD_SUCCESS = 'asset-upload/FILE_UPLOAD_SUCCESS';
export type OnFileUploadSuccessAction = {
  type: typeof FILE_UPLOAD_SUCCESS;
  payload: {line: Line; file: FileModel};
};
export const fileUploadSuccessAction = (line: Line, file: FileModel): OnFileUploadSuccessAction => ({
  type: FILE_UPLOAD_SUCCESS,
  payload: {
    line,
    file,
  },
});

export const FILE_UPLOAD_PROGRESS = 'asset-upload/FILE_UPLOAD_PROGRESS';
export type OnFileUploadProgressAction = {
  type: typeof FILE_UPLOAD_PROGRESS;
  payload: {line: Line; progress: number};
};
export const fileUploadProgressAction = (line: Line, progress: number): OnFileUploadProgressAction => ({
  type: FILE_UPLOAD_PROGRESS,
  payload: {
    line,
    progress,
  },
});

export const FILE_THUMBNAIL_GENERATION = 'asset-upload/THUMBNAIL_GENERATED';
export type OnFileThumbnailGenerationAction = {
  type: typeof FILE_THUMBNAIL_GENERATION;
  payload: {
    thumbnail: Thumbnail;
    line: Line;
  };
};
export const fileThumbnailGenerationAction = (thumbnail: Thumbnail, line: Line): OnFileThumbnailGenerationAction => ({
  type: FILE_THUMBNAIL_GENERATION,
  payload: {
    thumbnail,
    line,
  },
});

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
  console.log(state, action);

  switch (action.type) {
    case ADD_LINES:
      return {...state, lines: addLines(state.lines, action.payload.lines)};
    case FILE_THUMBNAIL_GENERATION:
      return {...state, lines: addThumbnail(state.lines, action.payload.line, action.payload.thumbnail)};
    case REMOVE_LINE:
      return {...state, lines: removeLine(state.lines, action.payload.line)};
    case REMOVE_ALL_LINES:
      return {...state, lines: []};
    case EDIT_LINE:
      return {...state, lines: editLine(state.lines, action.payload.line)};
    case FILE_UPLOAD_SUCCESS:
      return {...state, lines: addUploadedFileToLine(state.lines, action.payload.line, action.payload.file)};
    case FILE_UPLOAD_PROGRESS:
      return {...state, lines: updateUploadProgressToLine(state.lines, action.payload.line, action.payload.progress)};
    case LINE_CREATION_START:
      return {...state, lines: lineCreationStart(state.lines, action.payload.line)};
    case ASSET_CREATION_SUCCESS:
      return {...state, lines: assetCreationSucceeded(state.lines, action.payload.asset)};
    case ASSET_CREATION_FAIL:
      return {...state, lines: assetCreationFailed(state.lines, action.payload.asset, action.payload.errors)};
    default:
      return state;
  }
};
