import React from 'react';
import {
  Field,
  MediaFileInput,
  useBooleanState,
  IconButton,
  DownloadIcon,
  useInModal,
  FullscreenIcon,
} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeFieldProps, isImageAttributeInputValue} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';
import {memoize} from 'lodash/fp';
import {useTranslate, useUploader, useRouter} from '@akeneo-pim-community/shared';
import {usePreventClosing} from '../../hooks/usePreventClosing';
import {FullscreenPreview} from '../file/preview/fullscreen-preview';
import {getImageDownloadUrl, getMediaPreviewUrl} from '../../tools/media-url-generator';
import {MediaPreviewType} from '../../models/MediaPreview';

const unMemoizedBuildImageFieldAttribute: AttributeFieldBuilder<AttributeInputValue> = attribute => {
  const Component: React.FC<AttributeFieldProps<AttributeInputValue>> = ({
    channel,
    locale,
    value,
    onChange,
  }: AttributeFieldProps<AttributeInputValue>) => {
    const translate = useTranslate();
    const [uploader, isUploading] = useUploader('pim_enriched_category_rest_file_upload');
    usePreventClosing(() => isUploading, translate('pim_enrich.confirmation.discard_changes', {entity: 'category'}));
    const router = useRouter();
    const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();
    const inModal = useInModal();

    const imageInfo = !isImageAttributeInputValue(value) ? null : value;
    const downloadFilename = imageInfo?.originalFilename;
    const downloadUrl = imageInfo ? getImageDownloadUrl(router, imageInfo) : '';
    const thumbnailUrl = getMediaPreviewUrl(router, {
      type: MediaPreviewType.Thumbnail,
      attributeCode: attribute.code,
      data: imageInfo ? imageInfo.filePath : '',
    });

    return (
      <Field channel={channel.label} label={getLabelFromAttribute(attribute, locale)} locale={locale}>
        <MediaFileInput
          value={imageInfo}
          onChange={onChange}
          placeholder={translate('pim_common.media_upload')}
          uploadingLabel={translate('pim_common.media_uploading')}
          uploadErrorLabel={translate('pim_common.media_upload_error')}
          clearTitle={translate('pim_common.clear_value')}
          thumbnailUrl={thumbnailUrl}
          uploader={uploader}
        >
          {downloadUrl && downloadFilename && (
            <IconButton
              href={downloadUrl}
              target="_blank"
              download={downloadFilename}
              icon={<DownloadIcon />}
              title="Download"
            />
          )}
          {!inModal && <IconButton onClick={openFullscreenModal} icon={<FullscreenIcon />} title="Fullscreen" />}
        </MediaFileInput>
        {isFullscreenModalOpen && !inModal && imageInfo && (
          <FullscreenPreview
            onClose={closeFullscreenModal}
            attribute={attribute}
            data={imageInfo}
            label={attribute.labels[locale]}
          />
        )}
      </Field>
    );
  };

  Component.displayName = 'ImageFieldAttribute';

  return Component;
};

export const buildImageFieldAttribute = memoize(unMemoizedBuildImageFieldAttribute);
