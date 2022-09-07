import React, {useCallback, useState} from 'react';
import {Field, FileInfo, MediaFileInput, SectionTitle, TextAreaInput, TextInput, Helper} from 'akeneo-design-system';
import {Locale, LocaleSelector, useTranslate} from '@akeneo-pim-community/shared';
import {useTemplate} from '../hooks';
import styled from 'styled-components';
import {Attribute} from "../models";

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
        switch (attribute.type) {
          case 'text':
            return (
              <Field label={attribute.labels[locale]} locale={locale}>
                <TextInput name={attribute.code} value="" onChange={handleTextChange(attributeDefinitions[attribute.code])} />
              </Field>
            );
          case 'richtext':
            return (
              <Field960 label={attribute.labels[locale]} locale={locale}>
                <TextAreaInput isRichText name={attribute.code} value="" onChange={handleTextChange(attributeDefinitions[attribute.code])} />
              </Field960>
            );
          case 'textarea':
            return (
              <Field label={attribute.labels[locale]} locale={locale}>
                <TextAreaInput name={attribute.code} value="" onChange={handleTextChange(attributeDefinitions[attribute.code])} />
              </Field>
            );
          case 'image':
            return (
              <Field label={attribute.labels[locale]}>
                <MediaFileInput
                  value={null}
                  onChange={handleImageChange(attributeDefinitions[attribute.code])}
                  placeholder="Drag and drop to upload or click here"
                  uploadingLabel="Uploading..."
                  uploadErrorLabel="An error occurred during upload"
                  clearTitle="Clear"
                  thumbnailUrl={null}
                  uploader={dumbUploader}
                />
              </Field>
            );
        }

        return null;
      })}
    </FormContainer>
  );
};
