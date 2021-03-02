import React, {useState} from 'react';
import styled, {FlattenSimpleInterpolation} from 'styled-components';
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
import {Action, DownloadAction} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import imageUploader from 'akeneoassetmanager/infrastructure/uploader/image';
import loadImage from 'akeneoassetmanager/tools/image-loader';
import {usePreventClosing} from 'akeneoassetmanager/application/hooks/prevent-closing';
import {emptyMediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {
  CloseIcon,
  FullscreenIcon,
  getColor,
  ImportIllustration,
  AkeneoThemedProps,
  useBooleanState,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const FileUploadContainer = styled(Container)`
  position: relative;
  flex-direction: row;

  :hover {
    ${ImportIllustration.animatedMixin as FlattenSimpleInterpolation}
  }
`;

const FileInput = styled.input`
  position: absolute;
  opacity: 0;
  width: 100%;
  height: 100%;
  cursor: ${({disabled, readOnly}) => (disabled || readOnly ? 'not-allowed' : 'pointer')};
  color: ${({disabled, readOnly}) => getColor('grey', disabled || readOnly ? 100 : 140)};
`;

const MediaFileLabel = styled.label<{readOnly: boolean} & AkeneoThemedProps>`
  flex-grow: 1;
  font-size: ${props => props.theme.fontSize.big};
  color: ${({readOnly}) => getColor('grey', readOnly ? 100 : 140)};
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'pointer')};
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

const MediaFileLabelPlaceholder = styled(MediaFileLabel)`
  color: ${({readOnly}) => getColor('grey', readOnly ? 100 : 120)};
`;

const ThumbnailPlaceholder = styled.div`
  width: 40px;
  height: 40px;
  margin-right: 15px;
  border: 1px solid ${getColor('grey', 60)};
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
  const [isUploading, startUploading, stopUploading] = useBooleanState();
  const translate = useTranslate();

  const upload = React.useCallback(
    async (file: File): Promise<void> => {
      if (undefined === file) {
        return;
      }

      startUploading();
      const fileReader = new FileReader();
      fileReader.readAsDataURL(file);

      try {
        const image = await imageUploader.upload(file, () => {});
        try {
          await loadImage(
            getMediaPreviewUrl({
              type: MediaPreviewType.Thumbnail,
              attributeIdentifier: value.attribute.identifier,
              data: image?.filePath || '',
            })
          );
        } catch (error) {
          console.error(error);
        }
        onChange(setValueData(value, image));
      } catch (error) {
        console.error(error);
      }
      stopUploading();
    },
    [value, onChange]
  );

  const handleDrop = (e: React.DragEvent<HTMLInputElement>) => upload(e.dataTransfer.files[0]);
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => e.target.files && upload(e.target.files[0]);

  usePreventClosing(() => isUploading, translate('pim_enrich.confirmation.discard_changes', {entity: 'asset'}));

  return isUploading ? (
    <FileUploadContainer>
      <div className="AknLoadingPlaceHolderContainer">
        <ThumbnailPlaceholder />
      </div>
      <MediaFileLabelPlaceholder readOnly={readOnly}>
        {translate(`pim_asset_manager.attribute.media_file.uploading`)}
      </MediaFileLabelPlaceholder>
    </FileUploadContainer>
  ) : (
    <FileUploadContainer>
      <FileInput
        id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
        type="file"
        onDrop={handleDrop}
        onChange={handleChange}
        disabled={readOnly}
        readOnly={readOnly}
      />
      <ThumbnailPlaceholder>
        <ImportIllustration size={40} />
      </ThumbnailPlaceholder>
      <MediaFileLabelPlaceholder readOnly={readOnly}>
        {translate(`pim_asset_manager.attribute.media_file.${readOnly ? 'read_only' : 'placeholder'}`)}
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
  const translate = useTranslate();
  if (!isMediaFileData(value.data) || !isMediaFileAttribute(value.attribute)) {
    return null;
  }

  const handleRemove = () => onChange(setValueData(value, createEmptyFile()));

  const mediaPreviewUrl = getMediaPreviewUrl({
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier: value.attribute.identifier,
    data: getMediaData(value.data),
  });
  const emptyMediaUrl = getMediaPreviewUrl(emptyMediaPreview());

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
      <Thumbnail
        src={mediaPreviewUrl}
        alt={translate('pim_asset_manager.attribute.media_type_preview')}
        onError={event => (event.target as HTMLInputElement).setAttribute('src', emptyMediaUrl)}
      />
      <MediaFileLabel readOnly={!canEditData}>{value.data?.originalFilename}</MediaFileLabel>
      <Actions>
        {canEditData && (
          <Action onClick={handleRemove}>
            <CloseIcon title={translate('pim_asset_manager.app.image.wide.remove')} size={20} />
          </Action>
        )}
        <DownloadAction size={20} data={value.data} attribute={value.attribute} />
        <FullscreenPreview anchor={Action} label={label} data={value.data} attribute={value.attribute}>
          <FullscreenIcon title={translate('pim_asset_manager.asset.button.fullscreen')} size={20} />
        </FullscreenPreview>
      </Actions>
    </Container>
  );
};

export const view = View;
