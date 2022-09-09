import React, {useCallback, useState} from 'react';
import {SectionTitle, Helper} from 'akeneo-design-system';
import {Locale, LocaleSelector, useTranslate} from '@akeneo-pim-community/shared';
import {useTemplate} from '../hooks';
import styled from 'styled-components';

import {Attribute, CategoryAttributeValueData, CategoryImageAttributeValueData, EnrichCategory} from '../models';
import {attributeFieldFactory, isImageAttributeInputValue} from './attributes/templateAttributesFactory';
import {AttributeInputValue} from './attributes/types';

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

export const EditAttributesForm = ({onAttributeValueChange}: Props) => {
  const [locale, setLocale] = useState('en_US');
  const translate = useTranslate();

  const handleTextChange = useCallback(
    (attribute: Attribute) => (value: AttributeInputValue) => {
      if (isImageAttributeInputValue(value)) {
        return;
      }
      onAttributeValueChange(attribute, locale, value);
    },
    [locale, onAttributeValueChange]
  );

  const handleImageChange = useCallback(
    (attribute: Attribute) => (value: AttributeInputValue) => {
      if (!isImageAttributeInputValue(value)) {
        return;
      }

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
    return <Helper level="error">{translate('akeneo.category.edition_form.template.fetching_failed')}</Helper>;
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
        const AttrComp = attributeFieldFactory(attribute);
        if (AttrComp === null) {
          return <Helper level="error">Could not find builder for {attribute.type} </Helper>;
        }
        const handleChange = attribute.type === 'image' ? handleImageChange(attribute) : handleTextChange(attribute);
        // TODO use real value
        const value =
          attribute.type !== 'image'
            ? 'toto_' + attribute.code
            : {filePath: '/path/to/file/toto.png', originalFilename: 'toto.png'};

        // TODO value field to fill with real value
        return <AttrComp locale={locale} value={value} onChange={handleChange} key={attribute.uuid}></AttrComp>;
      })}
    </FormContainer>
  );
};
