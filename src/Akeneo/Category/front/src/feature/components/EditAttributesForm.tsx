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

  // TODO change hardcoded value to use the template uuid
  const {data: template, isLoading, isError} = useTemplate('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

  if (isLoading) {
    return null; //TODO display loading info ?
  }

  if (isError) {
    return (
      <Helper level="error">
        {translate('akeneo.category.edition_form.template.fetching_failed')}
      </Helper>
    );
  }

  let attributesByOrder: Attribute[] = [];
  template?.attributes.forEach((attribute: Attribute) => {
    attributesByOrder[attribute.order] = attribute;
  });

  return (
    <FormContainer>
      <SectionTitle>
        <SectionTitle.Title>{translate('Attributes')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <LocaleSelector value={locale} values={locales} onChange={setLocale} />
      </SectionTitle>

      {attributesByOrder.map((attribute: Attribute) => {
        let attributeCode = attribute.code;
        let attributeUuid = attribute.uuid;
        let attributeLabel = attribute.labels[locale] ?? "["+attributeCode+"]";
        switch (attribute.type) {
          case 'text':
            return (
              <Field key={attributeUuid} label={attributeLabel} locale={locale}>
                <TextInput name={attributeCode} value="" onChange={handleTextChange(attributeDefinitions[attributeCode])} />
              </Field>
            );
          case 'richtext':
            return (
              <Field960 key={attributeUuid} label={attributeLabel} locale={locale}>
                <TextAreaInput isRichText name={attributeCode} value="" onChange={handleTextChange(attributeDefinitions[attributeCode])} />
              </Field960>
            );
          case 'textarea':
            return (
              <Field key={attributeUuid} label={attributeLabel} locale={locale}>
                <TextAreaInput name={attributeCode} value="" onChange={handleTextChange(attributeDefinitions[attributeCode])} />
              </Field>
            );
          case 'image':
            return (
              <Field key={attributeUuid} label={attributeLabel}>
                <MediaFileInput
                  value={null}
                  onChange={handleImageChange(attributeDefinitions[attributeCode])}
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
