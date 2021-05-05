import Line, {Thumbnail} from 'akeneoassetmanager/application/asset-upload/model/line';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/creation-asset';
import {ValidationError} from '@akeneo-pim-community/shared';

export const ADD_LINES = 'asset-upload/ADD_LINES';
export type OnAddLineAction = {
  type: typeof ADD_LINES;
  payload: {
    lines: Line[];
  };
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
  payload: {
    line: Line;
  };
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
  payload: {
    asset: CreationAsset;
  };
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
  payload: {
    asset: CreationAsset;
    errors: ValidationError[];
  };
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
  payload: {
    line: Line;
  };
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
  payload: {
    line: Line;
  };
};
export const editLineAction = (line: Line): OnEditLineAction => ({
  type: EDIT_LINE,
  payload: {
    line,
  },
});

export const FILE_UPLOAD_START = 'asset-upload/FILE_UPLOAD_START';
export type OnFileUploadStartAction = {
  type: typeof FILE_UPLOAD_START;
  payload: {
    line: Line;
  };
};
export const fileUploadStartAction = (line: Line): OnFileUploadStartAction => ({
  type: FILE_UPLOAD_START,
  payload: {
    line,
  },
});

export const FILE_UPLOAD_SUCCESS = 'asset-upload/FILE_UPLOAD_SUCCESS';
export type OnFileUploadSuccessAction = {
  type: typeof FILE_UPLOAD_SUCCESS;
  payload: {
    line: Line;
    file: FileModel;
  };
};
export const fileUploadSuccessAction = (line: Line, file: FileModel): OnFileUploadSuccessAction => ({
  type: FILE_UPLOAD_SUCCESS,
  payload: {
    line,
    file,
  },
});

export const FILE_UPLOAD_FAILURE = 'asset-upload/FILE_UPLOAD_FAILURE';
export type OnFileUploadFailureAction = {
  type: typeof FILE_UPLOAD_FAILURE;
  payload: {
    line: Line;
    errors: ValidationError[];
  };
};
export const fileUploadFailureAction = (line: Line, errors: ValidationError[]): OnFileUploadFailureAction => ({
  type: FILE_UPLOAD_FAILURE,
  payload: {
    line,
    errors,
  },
});

export const FILE_UPLOAD_PROGRESS = 'asset-upload/FILE_UPLOAD_PROGRESS';
export type OnFileUploadProgressAction = {
  type: typeof FILE_UPLOAD_PROGRESS;
  payload: {
    line: Line;
    progress: number;
  };
};
export const fileUploadProgressAction = (line: Line, progress: number): OnFileUploadProgressAction => ({
  type: FILE_UPLOAD_PROGRESS,
  payload: {
    line,
    progress,
  },
});

export const FILE_THUMBNAIL_GENERATION_DONE = 'asset-upload/FILE_THUMBNAIL_GENERATION_DONE';
export type OnFileThumbnailGenerationDoneAction = {
  type: typeof FILE_THUMBNAIL_GENERATION_DONE;
  payload: {
    thumbnail: Thumbnail;
    line: Line;
  };
};
export const fileThumbnailGenerationDoneAction = (
  thumbnail: Thumbnail,
  line: Line
): OnFileThumbnailGenerationDoneAction => ({
  type: FILE_THUMBNAIL_GENERATION_DONE,
  payload: {
    thumbnail,
    line,
  },
});
