import * as React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {isMediaFileAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {isValueEmpty, setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import {Container, Thumbnail, Actions} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {Action, DownloadAction} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {Fullscreen} from 'akeneoassetmanager/application/component/app/icon/fullscreen';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import Close from 'akeneoassetmanager/application/component/app/icon/close';
import Import from 'akeneoassetmanager/application/component/app/illustration/import';
import imageUploader from 'akeneoassetmanager/infrastructure/uploader/image';
import loadImage from 'akeneoassetmanager/tools/image-loader';
import {usePreventClosing} from 'akeneoassetmanager/application/hooks/prevent-closing';

const FileUploadContainer = styled(Container).attrs(() => ({className: 'AknImage-uploader'}))`
  position: relative;
  flex-direction: row;
`;

const FileInput = styled.input.attrs(() => ({type: 'file'}))`
  position: absolute;
  opacity: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;

  :read-only,
  :disabled {
    cursor: not-allowed;
    color: ${props => props.theme.color.grey100};
  }
`;

const MediaFileLabel = styled.label<{readOnly: boolean}>`
  flex-grow: 1;
  font-size: ${props => props.theme.fontSize.big};
  color: ${props => (props.readOnly ? props.theme.color.grey100 : props.theme.color.grey140)};
  cursor: ${props => (props.readOnly ? 'not-allowed' : 'auto')};
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

const MediaFileLabelPlaceholder = styled(MediaFileLabel)`
  color: ${props => (props.readOnly ? props.theme.color.grey100 : props.theme.color.grey120)};
`;

const ThumbnailPlaceholder = styled.div`
  width: 40px;
  height: 40px;
  margin-right: 15px;
  border: 1px solid ${props => props.theme.color.grey70};
  display: flex;
  flex-direction: column;
  justify-content: center;
`;

const FileUploader = ({
  value,
  readOnly,
  onChange,
}: {
  value: EditionValue;
  readOnly: boolean;
  onChange: (value: EditionValue) => void;
}) => {
  const [isUploading, setUploading] = React.useState<boolean>(false);

  const upload = React.useCallback(
    async (file: File): Promise<void> => {
      if (undefined === file) {
        return;
      }

      setUploading(true);
      const fileReader = new FileReader();
      fileReader.readAsDataURL(file);

      try {
        const image = await imageUploader.upload(file, () => {});
        await loadImage(
          getMediaPreviewUrl({
            type: MediaPreviewType.Thumbnail,
            attributeIdentifier: value.attribute.identifier,
            data: image?.filePath || '',
          })
        );
        onChange(setValueData(value, image));
      } catch (error) {
        console.error(error);
      }
      setUploading(false);
    },
    [value, onChange]
  );

  const handleDrop = (e: React.DragEvent<HTMLInputElement>) => upload(e.dataTransfer.files[0]);
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => e.target.files && upload(e.target.files[0]);

  usePreventClosing(() => isUploading, __('pim_enrich.confirmation.discard_changes', {entity: 'asset'}));

  return isUploading ? (
    <FileUploadContainer>
      <div className="AknLoadingPlaceHolderContainer">
        <ThumbnailPlaceholder />
      </div>
      <MediaFileLabelPlaceholder readOnly={readOnly}>
        {__(`pim_asset_manager.attribute.media_file.uploading`)}
      </MediaFileLabelPlaceholder>
    </FileUploadContainer>
  ) : (
    <FileUploadContainer>
      <FileInput
        id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
        onDrop={handleDrop}
        onChange={handleChange}
        disabled={readOnly}
        readOnly={readOnly}
      />
      <ThumbnailPlaceholder>
        <Import />
      </ThumbnailPlaceholder>
      <MediaFileLabelPlaceholder readOnly={readOnly}>
        {__(`pim_asset_manager.attribute.media_file.${readOnly ? 'read_only' : 'placeholder'}`)}
      </MediaFileLabelPlaceholder>
    </FileUploadContainer>
  );
};

const View = ({
  value,
  locale,
  canEditData,
  onChange,
}: {
  value: EditionValue;
  locale: LocaleReference;
  canEditData: boolean;
  onChange: (value: EditionValue) => void;
}) => {
  if (!isMediaFileData(value.data) || !isMediaFileAttribute(value.attribute)) {
    return null;
  }

  const handleRemove = () => onChange(setValueData(value, createEmptyFile()));

  const mediaPreviewUrl = getMediaPreviewUrl({
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier: value.attribute.identifier,
    data: getMediaData(value.data),
  });

  const label = getLabelInCollection(
    value.attribute.labels,
    localeReferenceStringValue(locale),
    true,
    value.attribute.code
  );

  return isValueEmpty(value) ? (
    <FileUploader value={value} readOnly={!canEditData} onChange={onChange} />
  ) : (
    <Container>
      <Thumbnail src={mediaPreviewUrl} alt={__('pim_asset_manager.attribute.media_type_preview')} />
      <MediaFileLabel readOnly={!canEditData}>{value.data?.originalFilename}</MediaFileLabel>
      <Actions>
        {canEditData && (
          <Action onClick={handleRemove}>
            <Close title={__('pim_asset_manager.app.image.wide.remove')} size={20} color={akeneoTheme.color.grey100} />
          </Action>
        )}
        <DownloadAction color={akeneoTheme.color.grey100} size={20} data={value.data} attribute={value.attribute} />
        <FullscreenPreview anchor={Action} label={label} data={value.data} attribute={value.attribute}>
          <Fullscreen
            title={__('pim_asset_manager.asset.button.fullscreen')}
            color={akeneoTheme.color.grey100}
            size={20}
          />
        </FullscreenPreview>
      </Actions>
    </Container>
  );
};

export const view = View;
