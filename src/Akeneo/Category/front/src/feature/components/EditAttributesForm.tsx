import React, {useCallback, useMemo, useContext} from 'react';
import styled from 'styled-components';
import {SectionTitle, Helper} from 'akeneo-design-system';
import {LocaleSelector, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {
  Attribute,
  buildCompositeKey,
  CategoryAttributeValueData,
  CATEGORY_ATTRIBUTE_TYPE_RICHTEXT,
  EnrichCategory,
  isCategoryImageAttributeValueData,
  RICH_TEXT_DEFAULT_VALUE,
  Template,
} from '../models';
import {attributeFieldFactory} from './attributes/templateAttributesFactory';
import {AttributeInputValue, buildDefaultAttributeInputValue, isImageAttributeInputValue} from './attributes/types';
import {
  convertCategoryImageAttributeValueDataToFileInfo,
  convertFileInfoToCategoryImageAttributeValueData,
  getAttributeValue,
} from '../helpers';
import {EditCategoryContext} from './providers';

interface Props {
  attributeValues: EnrichCategory['attributes'];
  template: Template;
  onAttributeValueChange: (
    attribute: Attribute,
    locale: string | null,
    attributeValue: CategoryAttributeValueData
  ) => void;
  locale: string;
  setLocale: React.Dispatch<React.SetStateAction<string>>;
}

const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

function mustChangeBeSkipped(
  newValue: AttributeInputValue,
  currentValueInModel: CategoryAttributeValueData,
  attribute: Attribute
): boolean {
  // The RichTextEditor component triggers a call to onChange when focusing while value prop is ''
  // the bore value is then '<p></p>\n' and must be ignored or we will have
  // warnings concerning unsaved changed altough the user did change nothing
  return (
    attribute.type === CATEGORY_ATTRIBUTE_TYPE_RICHTEXT && !currentValueInModel && newValue === RICH_TEXT_DEFAULT_VALUE
  );
}

export const EditAttributesForm = ({attributeValues, template, onAttributeValueChange, locale, setLocale}: Props) => {
  const userContext = useUserContext();
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
      if (mustChangeBeSkipped(value, currentValue!, attribute)) {
        return;
      }

      onAttributeValueChange(attribute, locale, value);
    },
    [attributeValues, locale, onAttributeValueChange]
  );

  const handlers = useMemo(() => {
    const handlersMap: {[attributeUUID: string]: (value: AttributeInputValue) => void} = {};
    template?.attributes.forEach((attribute: Attribute) => {
      handlersMap[attribute.code] = handleChange(attribute);
    });
    return handlersMap;
  }, [template, handleChange]);

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
        <SectionTitle.Title>{translate('akeneo.category.attributes')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <LocaleSelector
          value={locale}
          values={Object.values(locales)}
          onChange={value => {
            setLocale(value);
            userContext.set('catalogLocale', value, {});
          }}
        />
      </SectionTitle>
      {attributeFields}
    </FormContainer>
  );
};
