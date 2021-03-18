import React from 'react';
import {
  MediaFileInput,
  FileInfo,
  IconButton,
  DownloadIcon,
  FullscreenIcon,
  useBooleanState,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useUploader} from '@akeneo-pim-community/shared';
import {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {isMediaFileAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import {getImageDownloadUrl, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {ViewGeneratorProps} from 'akeneoassetmanager/application/configuration/value';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {usePreventClosing} from 'akeneoassetmanager/application/hooks/prevent-closing';

const View = ({id, value, locale, canEditData, onChange, invalid}: ViewGeneratorProps) => {
  const translate = useTranslate();
  const [isUploading, uploader] = useUploader('akeneo_asset_manager_file_upload');
  usePreventClosing(() => isUploading, translate('pim_enrich.confirmation.discard_changes', {entity: 'asset'}));

  const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();
  if (!isMediaFileData(value.data) || !isMediaFileAttribute(value.attribute)) {
    return null;
  }

  if (id === undefined) {
    id = `pim_asset_manager.asset.enrich.${value.attribute.code}`;
  }

  const attributeLabel = getLabelInCollection(
    value.attribute.labels,
    localeReferenceStringValue(locale),
    true,
    value.attribute.code
  );

  const handleChange = (fileInfo: FileInfo) => {
    onChange(setValueData(value, fileInfo));
  };

  const downloadFilename = value.data?.originalFilename;
  const downloadUrl = getImageDownloadUrl(value.data);
  const previewUrl = getMediaPreviewUrl({
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier: value.attribute.identifier,
    data: getMediaData(value.data),
  });

  return (
    <>
      <MediaFileInput
        id={id}
        value={value.data}
        onChange={handleChange}
        thumbnailUrl={previewUrl}
        readOnly={!canEditData}
        placeholder={!canEditData ? '' : translate('pim_asset_manager.attribute.media_file.placeholder')}
        invalid={invalid}
        uploader={uploader}
        uploadingLabel={translate('pim_asset_manager.attribute.media_file.uploading')}
        clearTitle={translate('pim_common.clear')}
        size="small"
        uploadErrorLabel={translate('pim_asset_manager.asset.upload.upload_failure')}
      >
        <IconButton
          href={downloadUrl}
          target="_blank"
          download={downloadFilename}
          icon={<DownloadIcon />}
          title={translate('pim_asset_manager.asset_preview.download')}
        />
        <IconButton
          onClick={openFullscreenModal}
          icon={<FullscreenIcon />}
          title={translate('pim_asset_manager.asset.button.fullscreen')}
        />
      </MediaFileInput>
      {isFullscreenModalOpen && (
        <FullscreenPreview
          onClose={closeFullscreenModal}
          attribute={value.attribute}
          data={value.data}
          label={attributeLabel}
        />
      )}
    </>
  );
};

export const view = View;
