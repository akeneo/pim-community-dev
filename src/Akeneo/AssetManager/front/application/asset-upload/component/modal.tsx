import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {Modal, Header, Title, ConfirmButton} from 'akeneoassetmanager/application/component/app/modal';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import LineList from 'akeneoassetmanager/application/asset-upload/component/line-list';
import {
  createLineFromFilename,
  addLines,
  addThumbnail,
  removeLine,
  addUploadedFileToLine,
  updateUploadProgressToLine,
} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import Line, {Thumbnail} from 'akeneoassetmanager/application/asset-upload/model/line';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import imageUploader from 'akeneoassetmanager/infrastructure/uploader/image';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';

type UploadModalProps = {
  assetFamily: AssetFamily;
  onCancel: () => void;
  onAssetCreated: () => void;
};

const getThumbnailFromFile = async (file: File, line: Line): Promise<{thumbnail: Thumbnail; line: Line}> => {
  return new Promise((resolve: ({thumbnail, line}: {thumbnail: Thumbnail; line: Line}) => void) => {
    var fileReader = new FileReader();
    if (file.type.match('image')) {
      fileReader.onload = () => {
        resolve({thumbnail: fileReader.result as string, line});
      };
      fileReader.readAsDataURL(file);
    } else {
      resolve({thumbnail: null, line});
    }
  });
};

type State = {
  lines: Line[];
};

const ADD_LINES = 'asset-upload/ADD_LINES';
type OnAddLineAction = {
  type: typeof ADD_LINES;
  payload: {lines: Line[]};
};
const linesAddedAction = (lines: Line[]): OnAddLineAction => ({
  type: ADD_LINES,
  payload: {
    lines,
  },
});

const REMOVE_LINE = 'asset-upload/REMOVE_LINE';
type OnRemoveLineAction = {
  type: typeof REMOVE_LINE;
  payload: {line: Line};
};
const removeLineAction = (line: Line): OnRemoveLineAction => ({
  type: REMOVE_LINE,
  payload: {
    line,
  },
});

const FILE_UPLOAD_SUCCESS = 'asset-upload/FILE_UPLOAD_SUCCESS';
type OnFileUploadSuccessAction = {
  type: typeof FILE_UPLOAD_SUCCESS;
  payload: {line: Line; file: FileModel};
};
const fileUploadSuccessAction = (line: Line, file: FileModel): OnFileUploadSuccessAction => ({
  type: FILE_UPLOAD_SUCCESS,
  payload: {
    line,
    file,
  },
});

const FILE_UPLOAD_PROGRESS = 'asset-upload/FILE_UPLOAD_PROGRESS';
type OnFileUploadProgressAction = {
  type: typeof FILE_UPLOAD_PROGRESS;
  payload: {line: Line; progress: number};
};
const fileUploadProgressAction = (line: Line, progress: number): OnFileUploadProgressAction => ({
  type: FILE_UPLOAD_PROGRESS,
  payload: {
    line,
    progress,
  },
});

const FILE_THUMBNAIL_GENERATION = 'asset-upload/THUMBNAIL_GENERATED';
type OnFileThumbnailGenerationAction = {
  type: typeof FILE_THUMBNAIL_GENERATION;
  payload: {
    thumbnail: Thumbnail;
    line: Line;
  };
};
const fileThumbnailGenerationAction = (thumbnail: Thumbnail, line: Line): OnFileThumbnailGenerationAction => ({
  type: FILE_THUMBNAIL_GENERATION,
  payload: {
    thumbnail,
    line,
  },
});

const reducer = (
  state: State,
  action:
    | OnAddLineAction
    | OnFileThumbnailGenerationAction
    | OnRemoveLineAction
    | OnFileUploadSuccessAction
    | OnFileUploadProgressAction
) => {
  console.log(state, action);

  switch (action.type) {
    case ADD_LINES:
      return {...state, lines: addLines(state.lines, action.payload.lines)};
    case FILE_THUMBNAIL_GENERATION:
      return {...state, lines: addThumbnail(state.lines, action.payload.line, action.payload.thumbnail)};
    case REMOVE_LINE:
      return {...state, lines: removeLine(state.lines, action.payload.line)};
    case FILE_UPLOAD_SUCCESS:
      return {...state, lines: addUploadedFileToLine(state.lines, action.payload.line, action.payload.file)};
    case FILE_UPLOAD_PROGRESS:
      return {...state, lines: updateUploadProgressToLine(state.lines, action.payload.line, action.payload.progress)};
    default:
      return state;
  }
};

const uploadFile = async (
  file: File,
  line: Line,
  updateProgress: (line: Line, progress: number) => void
): Promise<FileModel | null> => {
  return new Promise((resolve: (file: FileModel) => void, reject: (validation: ValidationError[]) => void) => {
    if (undefined === file) {
      resolve(null);
    }

    updateProgress(line, 0);

    try {
      imageUploader
        .upload(file, (ratio: number) => {
          updateProgress(line, ratio);
        })
        .then(resolve);
    } catch (error) {
      reject(error);
    }
  });
};

const UploadModal = ({assetFamily, onCancel, onAssetCreated}: UploadModalProps) => {
  const [state, dispatch] = React.useReducer(reducer, {lines: []});

  return (
    <Modal>
      <Header>
        <CloseButton title={__('pim_asset_manager.close')} onClick={onCancel} />
        <Title>{__('pim_asset_manager.asset.upload.title')}</Title>
        <ConfirmButton title={__('pim_asset_manager.asset.upload.confirm')} color="green" onClick={onAssetCreated}>
          {__('pim_asset_manager.asset.upload.confirm')}
        </ConfirmButton>
      </Header>
      <input
        type="file"
        multiple
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          event.preventDefault();
          event.stopPropagation();

          const files = event.target.files;
          if (null === files) {
            return;
          }
          const lines = Object.values(files).map((file: File) => {
            const filename = file.name;

            const line = createLineFromFilename(filename, assetFamily);
            getThumbnailFromFile(file, line).then(({thumbnail, line}) =>
              dispatch(fileThumbnailGenerationAction(thumbnail, line))
            );

            uploadFile(file, line, (line: Line, progress: number) => {
              dispatch(fileUploadProgressAction(line, progress));
            }).then((file: FileModel) => {
              dispatch(fileUploadSuccessAction(line, file));
            });

            return line;
          });
          dispatch(linesAddedAction(lines));
        }}
      />
      <LineList
        lines={state.lines}
        onLineRemove={(line: Line) => {
          dispatch(removeLineAction(line));
        }}
      />
    </Modal>
  );
};

export default UploadModal;
