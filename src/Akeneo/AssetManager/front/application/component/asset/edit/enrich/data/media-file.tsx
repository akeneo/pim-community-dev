import React from 'react';
import {MediaFileInput, FileInfo} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useImageUploader} from '@akeneo-pim-community/shared';
import {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {isMediaFileAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import {getImageDownloadUrl, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {ViewGeneratorProps} from 'akeneoassetmanager/application/configuration/value';

const View = ({value, locale, canEditData, onChange, invalid}: ViewGeneratorProps) => {
  const translate = useTranslate();
  const uploader = useImageUploader('akeneo_asset_manager_file_upload');
  if (!isMediaFileData(value.data) || !isMediaFileAttribute(value.attribute)) {
    return null;
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

  return (
    <MediaFileInput
      value={value.data}
      onChange={handleChange}
      previewer={(file, type) =>
        getMediaPreviewUrl({
          type: type as MediaPreviewType,
          attributeIdentifier: value.attribute.identifier,
          data: file.filePath,
        })
      }
      readOnly={!canEditData}
      placeholder={translate(`pim_asset_manager.attribute.media_file.${!canEditData ? 'read_only' : 'placeholder'}`)}
      invalid={invalid}
      uploader={uploader}
      downloader={fileInfo => getImageDownloadUrl(fileInfo)}
      fullscreenLabel={attributeLabel}
      uploadingLabel={translate('pim_asset_manager.attribute.media_file.uploading')}
      downloadLabel={translate('pim_asset_manager.asset_preview.download')}
      clearTitle={translate('pim_common.clear')}
      fullscreenTitle={translate('pim_asset_manager.asset.button.fullscreen')}
      closeTitle={translate('pim_common.close')}
      size="small"
      uploadErrorLabel={translate('pim_asset_manager.asset.upload.upload_failure')}
    />
  );
};

export const view = View;
