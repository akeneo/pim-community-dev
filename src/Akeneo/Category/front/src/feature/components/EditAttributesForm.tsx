import React, {useCallback, useState} from 'react';
import styled from 'styled-components';
import {Locale, LocaleSelector, useRouter, useTranslate, useUploader} from '@akeneo-pim-community/shared';
import {
  Field,
  MediaFileInput,
  SectionTitle,
  TextAreaInput,
  TextInput,
  Helper, useBooleanState, IconButton, DownloadIcon, useInModal, FullscreenIcon,
} from 'akeneo-design-system';
import {useTemplate} from '../hooks';
import {
  Attribute,
  File,
  buildCompositeKey,
  CategoryAttributeValueData,
  CategoryImageAttributeValueData,
  EnrichCategory,
} from '../models';
import {usePreventClosing} from '../hooks/usePreventClosing';
import {getImageDownloadUrl, getMediaPreviewUrl} from '../tools/media-url-generator';
import {MediaPreviewType} from '../models/MediaPreview';
import {FullscreenPreview} from './file/preview/fullscreen-preview';

const locales: Locale[] = [
  {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  },
  {
    code: 'fr_FR',
    label: 'French (France)',
    region: 'France',
    language: 'French',
  },
];

interface Props {
  attributeValues: EnrichCategory['attributes'];
  onAttributeValueChange: (
    attribute: Attribute,
    locale: string | null,
    attributeValue: CategoryAttributeValueData
  ) => void;
}

const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

const Field960 = styled(Field)`
  max-width: 960px;
`;

export const EditAttributesForm = ({attributeValues, onAttributeValueChange}: Props) => {
  const [locale, setLocale] = useState('en_US');
  const translate = useTranslate();
  const router = useRouter();
  const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();
  const inModal = useInModal();
  const [uploader, isUploading] = useUploader('pim_enriched_category_rest_file_upload');
  usePreventClosing(() => isUploading, translate('pim_enrich.confirmation.discard_changes', {entity: 'category'}));

  const handleTextChange = useCallback(
    (attribute: Attribute) => (value: string) => {
      onAttributeValueChange(attribute, locale, value);
    },
    [locale, onAttributeValueChange]
  );

  const handleImageChange = useCallback(
    (attribute: Attribute) => (value: File) => {
      if (value === null) {
        return;
      }

      const data: CategoryImageAttributeValueData = {
        size: value.size,
        file_path: value.filePath,
        mime_type: value.mimeType,
        extension: value.extension,
        original_filename: value.originalFilename,
      };
      onAttributeValueChange(attribute, locale, data);
    },
    [locale, onAttributeValueChange]
  );

  const getAttributeValues = useCallback(
    (attribute: Attribute): any => {
      const compositeKey = buildCompositeKey(attribute, locale);

      return attributeValues[compositeKey] && attributeValues[compositeKey].data ? attributeValues[compositeKey].data : null;
    },
    [attributeValues, locale]
  );

  const {data: template, isLoading, isError, error} = useTemplate('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

  if (isLoading) {
    return null; //TODO
  }

  if (isError) {
    console.log(error); //TODO
    return (
      <Helper level="error">
        {error?.message}
      </Helper>
    );
  }

  return (
    <FormContainer>
      <SectionTitle>
        <SectionTitle.Title>{translate('Attributes')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <LocaleSelector value={locale} values={locales} onChange={setLocale} />
      </SectionTitle>

      {template?.attributes.map((attribute: Attribute) => {
        const value = getAttributeValues(attribute);

        switch (attribute.type) {
          case 'text':
            return (
              <Field key={attribute.uuid} label={attribute.labels[locale]} locale={locale}>
                <TextInput name={attribute.code} value={typeof value === 'string' ? value : ''} onChange={handleTextChange(attribute)} />
              </Field>
            );
          case 'richtext':
            return (
              <Field960 key={attribute.uuid} label={attribute.labels[locale]} locale={locale}>
                <TextAreaInput isRichText name={attribute.code} value={typeof value === 'string' ? value : ''} onChange={handleTextChange(attribute)} />
              </Field960>
            );
          case 'textarea':
            return (
              <Field key={attribute.uuid} label={attribute.labels[locale]} locale={locale}>
                <TextAreaInput name={attribute.code} value={typeof value === 'string' ? value : ''} onChange={handleTextChange(attribute)} />
              </Field>
            );
          case 'image':
            let fileInfo = null;
            if (value && typeof value === 'object') {
              fileInfo = {
                size: value.size,
                filePath: value.file_path,
                mimeType: value.mime_type,
                extension: value.extension,
                originalFilename: value.original_filename,
              }
            }

            const downloadFilename = fileInfo?.originalFilename;
            const downloadUrl = fileInfo ? getImageDownloadUrl(router, fileInfo) : '';
            const previewUrl = getMediaPreviewUrl(router, {
              type: MediaPreviewType.Thumbnail,
              attributeIdentifier: attribute.code + '|' + attribute.uuid,
              data: fileInfo?.filePath
            });

            return (
              <Field key={attribute.uuid} label={attribute.labels[locale]}>
                <MediaFileInput
                  value={fileInfo}
                  onChange={handleImageChange(attribute)}
                  placeholder="Drag and drop to upload or click here"
                  uploadingLabel="Uploading..."
                  uploadErrorLabel="An error occurred when uploading the file."
                  clearTitle="Clear"
                  thumbnailUrl={previewUrl}
                  uploader={uploader}
                >
                  <IconButton
                    href={downloadUrl}
                    target="_blank"
                    download={downloadFilename}
                    icon={<DownloadIcon />}
                    title="Download"
                  />
                  {!inModal && (
                    <IconButton
                      onClick={openFullscreenModal}
                      icon={<FullscreenIcon />}
                      title="Fullscreen"
                    />
                  )}
                </MediaFileInput>
                {isFullscreenModalOpen && !inModal && fileInfo && (
                  <FullscreenPreview
                    onClose={closeFullscreenModal}
                    attribute={value}
                    data={fileInfo}
                    label={attribute.labels[locale]}
                  />
                )}
              </Field>
            );
        }

        return null;
      })}
    </FormContainer>
  );
};
