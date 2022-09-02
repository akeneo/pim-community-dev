import React, {useCallback, useState} from 'react';
import styled from 'styled-components';

import {Field, FileInfo, MediaFileInput, SectionTitle, TextAreaInput, TextInput} from 'akeneo-design-system';
import {Locale, LocaleSelector, useTranslate} from '@akeneo-pim-community/shared';

import {
  CategoryAttributeDefinition,
  CategoryAttributeValueData,
  CategoryImageAttributeValueData,
  EnrichCategory,
} from '../models';
import {attributeDefinitions} from '../models/TemplateMocking';

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
  attributes: EnrichCategory['attributes'];
  onAttributeValueChange: (
    attribute: CategoryAttributeDefinition,
    locale: string | null,
    attributeValue: CategoryAttributeValueData
  ) => void;
}

const dumbUploader = async (file: File, onProgress: (ratio: number) => void) => ({
  filePath: 'foo',
  originalFilename: 'bar',
});

const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

const Field960 = styled(Field)`
  max-width: 960px;
`;

export const EditAttributesForm = ({onAttributeValueChange}: Props) => {
  const [locale, setLocale] = useState('en_US');
  const translate = useTranslate();

  const handleTextChange = useCallback(
    (attribute: CategoryAttributeDefinition) => (value: string) => {
      onAttributeValueChange(attribute, locale, value);
    },
    [locale, onAttributeValueChange]
  );

  const handleImageChange = useCallback(
    (attribute: CategoryAttributeDefinition) => (value: FileInfo | null) => {
      // TODO handle value===null
      if (!value || !value.size || !value.mimeType || !value.extension) {
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

  return (
    <FormContainer>
      <SectionTitle>
        <SectionTitle.Title>{translate('Attributes')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <LocaleSelector value={locale} values={locales} onChange={setLocale} />
      </SectionTitle>
      <Field960 label="Description" locale={locale}>
        <TextAreaInput
          isRichText
          name="description"
          value=""
          onChange={handleTextChange(attributeDefinitions['description'])}
        />
      </Field960>
      <Field label="Banner Image">
        <MediaFileInput
          value={null}
          onChange={handleImageChange(attributeDefinitions['banner'])}
          placeholder="Drag and drop to upload or click here"
          uploadingLabel="Uploading..."
          uploadErrorLabel="An error occurred during upload"
          clearTitle="Clear"
          thumbnailUrl={null}
          uploader={dumbUploader}
        />
      </Field>
      <Field label="SEO Meta Title" locale={locale}>
        <TextInput name="seo_meta_title" value="" onChange={handleTextChange(attributeDefinitions['seo_meta_title'])} />
      </Field>
      <Field label="SEO Meta Description" locale={locale}>
        <TextAreaInput
          name="seo_meta_description"
          value=""
          onChange={handleTextChange(attributeDefinitions['seo_meta_description'])}
        />
      </Field>
      <Field label="SEO Keywords" locale={locale}>
        <TextAreaInput name="seo_keywords" value="" onChange={handleTextChange(attributeDefinitions['seo_keywords'])} />
      </Field>
    </FormContainer>
  );
};
