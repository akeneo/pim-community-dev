import React, {useCallback, useMemo, useContext, useState} from 'react';
import styled from 'styled-components';
import {SectionTitle, Helper} from 'akeneo-design-system';
import {LocaleSelector, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {ChannelSelector} from './channel';
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
    channel: string | null,
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

export const EditAttributesForm = ({attributeValues, template, onAttributeValueChange}: Props) => {
  const translate = useTranslate();
  const {channels} = useContext(EditCategoryContext);
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [locale, setLocale] = useState(catalogLocale);
  const catalogChannel = userContext.get('catalogScope');
  const [channel, setChannel] = useState(catalogChannel);
  const channelList = useMemo(() => Object.values(channels), [channels]);

  const handleLocaleChange = (value: string): void => {
    setLocale(value);
    userContext.set('catalogLocale', value, {});
  };

  const handleChannelChange = (value: string): void => {
    setChannel(value);
    userContext.set('catalogScope', value, {});
    const localesFromChannel = channels[value]?.locales;
    const localeFromChannel = localesFromChannel?.find(channelLocale => locale === channelLocale.code);
    if (!localeFromChannel) {
      handleLocaleChange(localesFromChannel[0].code);
    }
  };

  const handleChange = useCallback(
    (attribute: Attribute) => (value: AttributeInputValue) => {
      if (isImageAttributeInputValue(value)) {
        onAttributeValueChange(attribute, channel, locale, convertFileInfoToCategoryImageAttributeValueData(value));
        return;
      }

      // attribute has textual type
      const currentValue = getAttributeValue(attributeValues, attribute, channel, locale);
      if (mustChangeBeSkipped(value, currentValue!, attribute)) {
        return;
      }

      onAttributeValueChange(attribute, channel, locale, value);
    },
    [attributeValues, channel, locale, onAttributeValueChange]
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

    const effectiveChannelCode = attribute.is_scopable ? channel : null;
    const effectiveLocaleCode = attribute.is_localizable ? locale : null;
    const compositeKey = buildCompositeKey(attribute, effectiveChannelCode, effectiveLocaleCode);

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
        channel={channel}
        locale={locale}
        value={dataForInput}
        onChange={handlers[attribute.code]}
        key={attribute.uuid}
      ></AttributeField>
    );
  });

  return (
    <FormContainer>
      <SectionTitle sticky={44}>
        <SectionTitle.Title>{translate('akeneo.category.attributes')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <ChannelSelector value={channel} values={channelList} onChange={handleChannelChange} />
        <LocaleSelector value={locale} values={channels[channel]?.locales} onChange={handleLocaleChange} />
      </SectionTitle>
      {attributeFields}
    </FormContainer>
  );
};
