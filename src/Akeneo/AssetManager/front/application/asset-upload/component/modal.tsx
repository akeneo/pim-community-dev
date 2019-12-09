import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {Modal, Header, ConfirmButton} from 'akeneoassetmanager/application/component/app/modal';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import LineList from 'akeneoassetmanager/application/asset-upload/component/line-list';
import {
  createLineFromFilename,
  selectLinesToSend,
  createAssetsFromLines,
} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import Line, {Thumbnail} from 'akeneoassetmanager/application/asset-upload/model/line';
import {AssetFamily, getAssetFamilyMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import imageUploader from 'akeneoassetmanager/infrastructure/uploader/image';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {NormalizedValidationError as ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';
import {create} from 'akeneoassetmanager/application/asset-upload/saver/asset';
import {
  lineCreationStartAction,
  fileThumbnailGenerationAction,
  fileUploadProgressAction,
  linesAddedAction,
  removeLineAction,
  fileUploadSuccessAction,
  reducer,
  assetCreationFailAction,
  assetCreationSuccessAction,
  OnFileThumbnailGenerationAction,
  OnFileUploadSuccessAction,
  OnFileUploadProgressAction,
  OnAddLineAction,
  editLineAction,
  removeAllLinesAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/asset-upload';
import FileDropZone from 'akeneoassetmanager/application/asset-upload/component/file-drop-zone';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';

const Subtitle = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  margin-bottom: 12px;
  text-align: center;
  text-transform: uppercase;
  width: 100%;
`;

const Title = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  line-height: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  margin-bottom: 33px;
  text-align: center;
  width: 100%;
`;

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

const onCreateAllAssetAction = (assetFamily: AssetFamily, lines: Line[], dispatch: (action: any) => void) => {
  const linesToSend = selectLinesToSend(lines);
  const assetsToSend = createAssetsFromLines(linesToSend, assetFamily);

  linesToSend.forEach((line: Line) => dispatch(lineCreationStartAction(line)));

  assetsToSend.forEach(async (asset: CreationAsset) => {
    try {
      const result = await create(asset);

      if (null !== result) {
        dispatch(assetCreationFailAction(asset, result));
      } else {
        dispatch(assetCreationSuccessAction(asset));
      }
    } catch (e) {
      dispatch(
        assetCreationFailAction(asset, [
          {
            messageTemplate: 'pim_asset_manager.asset.validation.server_error',
            parameters: {},
            message: 'Internal server error',
            propertyPath: '',
            invalidValue: asset,
          },
        ])
      );
    }
  });
};

const onFileDrop = (
  files: FileList | null,
  assetFamily: AssetFamily,
  dispatch: (
    action: OnFileThumbnailGenerationAction | OnFileUploadProgressAction | OnFileUploadSuccessAction | OnAddLineAction
  ) => void
) => {
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
};

const UploadModal = ({assetFamily, onCancel}: UploadModalProps) => {
  const [state, dispatch] = React.useReducer(reducer, {lines: []});
  const attributeAsMainMedia = getAssetFamilyMainMedia(assetFamily) as NormalizedAttribute;

  return (
    <Modal>
      <Header>
        <CloseButton title={__('pim_asset_manager.close')} onClick={onCancel} />
        {/* TODO retrieve the correct locale */}
        <Subtitle>{getAssetFamilyLabel(assetFamily, 'en_US', true)}</Subtitle>
        <Title>{__('pim_asset_manager.asset.upload.title')}</Title>
        <ConfirmButton
          title={__('pim_asset_manager.asset.upload.confirm')}
          color="green"
          onClick={() => {
            onCreateAllAssetAction(assetFamily, state.lines, dispatch);
          }}
        >
          {__('pim_asset_manager.asset.upload.confirm')}
        </ConfirmButton>
      </Header>
      <FileDropZone
        onDrop={(event: React.ChangeEvent<HTMLInputElement>) => {
          event.preventDefault();
          event.stopPropagation();

          const files = event.target.files;
          onFileDrop(files, assetFamily, dispatch);
        }}
      />
      <LineList
        lines={state.lines}
        onLineChange={(line: Line) => {
          dispatch(editLineAction(line));
        }}
        onLineRemove={(line: Line) => {
          dispatch(removeLineAction(line));
        }}
        onLineRemoveAll={() => {
          dispatch(removeAllLinesAction());
        }}
        valuePerLocale={attributeAsMainMedia.value_per_locale}
        valuePerChannel={attributeAsMainMedia.value_per_locale}
      />
    </Modal>
  );
};

export default UploadModal;
