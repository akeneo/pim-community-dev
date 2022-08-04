import React, {useState} from 'react';
import {Field, MediaFileInput, SectionTitle, TextAreaInput, TextInput} from 'akeneo-design-system';
import {LocaleSelector, useTranslate} from '@akeneo-pim-community/shared';
import {FormContainer} from './Style';
import styled from 'styled-components';

const locales = [
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

interface Props {}

const dumbHandler = (value: any) => value;
const dumbUploader = async (file: File, onProgress: (ratio: number) => void) => ({
  filePath: 'foo',
  originalFilename: 'bar',
});

const Field960 = styled(Field)`
  max-width: 960px;
`;

export const EditAttributesForm = (props: Props) => {
  const [locale, setLocale] = useState('en_US');
  const translate = useTranslate();

  return (
    <FormContainer>
      <SectionTitle>
        <SectionTitle.Title>{translate('Attributes')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <LocaleSelector value={locale} values={locales} onChange={setLocale} />
      </SectionTitle>
      <Field960 label="Description" locale={locale}>
        <TextAreaInput isRichText name="description" value="" onChange={dumbHandler} />
      </Field960>
      <Field label="Banner Image">
        <MediaFileInput
          value={null}
          onChange={dumbHandler}
          placeholder="Drag and drop to upload or click here"
          uploadingLabel="Uploading..."
          uploadErrorLabel="An error occurred during upload"
          clearTitle="Clear"
          thumbnailUrl={null}
          uploader={dumbUploader}
        />
      </Field>
      <Field label="SEO Meta Title" locale={locale}>
        <TextInput name="seo_meta_title" value="" onChange={dumbHandler} />
      </Field>
      <Field label="SEO Meta Description" locale={locale}>
        <TextAreaInput name="seo_meta_description" value="" onChange={dumbHandler} />
      </Field>
      <Field label="SEO Keywords" locale={locale}>
        <TextAreaInput name="seo_keywords" value="" onChange={dumbHandler} />
      </Field>
    </FormContainer>
  );
};
