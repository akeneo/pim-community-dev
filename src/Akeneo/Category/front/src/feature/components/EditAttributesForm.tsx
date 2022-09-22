import React, {useCallback, useState, useMemo, useContext} from 'react';
import styled from 'styled-components';
import {SectionTitle, Helper} from 'akeneo-design-system';
import {LocaleSelector, useTranslate} from '@akeneo-pim-community/shared';
import {useTemplate} from '../hooks';
import {
  Attribute,
  buildCompositeKey,
  CategoryAttributeValueData,
  CATEGORY_ATTRIBUTE_TYPE_RICHTEXT,
  EnrichCategory,
  getAttributeValue,
  isCategoryImageAttributeValueData,
} from '../models';
import {attributeFieldFactory} from './attributes/templateAttributesFactory';
import {AttributeInputValue, buildDefaultAttributeInputValue, isImageAttributeInputValue} from './attributes/types';
import {
  convertCategoryImageAttributeValueDataToFileInfo,
  convertFileInfoToCategoryImageAttributeValueData,
} from '../helpers';
import {EditCategoryContext} from './providers';

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

export const EditAttributesForm = ({attributeValues, onAttributeValueChange}: Props) => {
  const [locale, setLocale] = useState('en_US');
  const {locales} = useContext(EditCategoryContext);
  const translate = useTranslate();

  const handleChange = useCallback( 
    (attribute: Attribute) => (value: AttributeInputValue) => {
      if (isImageAttributeInputValue(value)) {
        onAttributeValueChange(attribute, locale, convertFileInfoToCategoryImageAttributeValueData(value));
        return;
      }

      // attribute has textual type
      const currentValue = getAttributeValue(attributeValues, attribute, locale);
      if (attribute.type === CATEGORY_ATTRIBUTE_TYPE_RICHTEXT
        && !currentValue
        && value === '<p></p>\n'
        ) {
          // The RichTextEditor component triggers a call to onChange when focusing while value prop is ''
          // the bore value is then '<p></p>\n' and must be ignored or we will have
          // warnings concerning unsaved changed altough the user did change nothing
          return;
        }
      onAttributeValueChange(attribute, locale, value);
    }, [attributeValues, locale, onAttributeValueChange]);
    

  // TODO change hardcoded value to use the template uuid
  const {data: template, isLoading, isError} = useTemplate('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

  const handlers = useMemo(() => {
    const handlersMap: {[attributeUUID: string]: (value: AttributeInputValue) => void} = {};
    template?.attributes.forEach((attribute: Attribute) => {
      handlersMap[attribute.code] = handleChange(attribute);
    });
    return handlersMap;
  }, [template, handleChange]);

  if (isLoading) {
    return <h1>{translate('LOADING ...')}</h1>;
  }

  if (isError) {
    return <Helper level="error">{translate('akeneo.category.edition_form.template.fetching_failed')}</Helper>;
  }

  const attributeFields = template?.attributes.map((attribute: Attribute) => {
    const AttributeField = attributeFieldFactory(attribute);

    if (AttributeField === null) {
      return (
        <Helper level="error">
          {translate('akeneo.category.edition_form.template.fetching_failed', {type: attribute.type})}
        </Helper>
      );
    }

    const effectiveLocaleCode = attribute.is_localizable ? locale : null;
    const compositeKey = buildCompositeKey(attribute, effectiveLocaleCode);

    let value = attributeValues[compositeKey];

    let dataForInput;
    if (value) {
      let {data: dataFromModel} = value;

      if (isCategoryImageAttributeValueData(dataFromModel)) {
        dataForInput = convertCategoryImageAttributeValueDataToFileInfo(dataFromModel);
      } else {
        dataForInput = dataFromModel;
      }
    } else {
      dataForInput = buildDefaultAttributeInputValue(attribute.type);
    }

    return (
      <AttributeField
        locale={locale}
        value={dataForInput}
        onChange={handlers[attribute.code]}
        key={attribute.uuid}
      ></AttributeField>
    );
  });

  return (
    <FormContainer>
      <SectionTitle>
        <SectionTitle.Title>{translate('Attributes')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <LocaleSelector value={locale} values={Object.values(locales)} onChange={setLocale} />
      </SectionTitle>
      {attributeFields}
    </FormContainer>
  );
};
