import React, {useCallback, useContext, useMemo, useState} from 'react';
import styled from 'styled-components';
import {Helper, SectionTitle} from 'akeneo-design-system';
import {LocaleSelector, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {ChannelSelector} from './channel';
import {
  Attribute,
  buildCompositeKey,
  CATEGORY_ATTRIBUTE_TYPE_RICHTEXT,
  CategoryAttributeValueData,
  EnrichCategory,
  isCategoryImageAttributeValueData,
  RICH_TEXT_DEFAULT_VALUE,
  Template,
} from '../models';
import {
  attributeFieldFactory,
  AttributeInputValue,
  buildDefaultAttributeInputValue,
  isImageAttributeInputValue,
} from './attributes';
import {
  convertCategoryImageAttributeValueDataToFileInfo,
  convertFileInfoToCategoryImageAttributeValueData,
  getAttributeValue,
  getChannelTranslation,
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
  onLocaleChange?: (locale: string) => void;
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

export const EditAttributesForm = ({attributeValues, template, onAttributeValueChange, onLocaleChange}: Props) => {
  const translate = useTranslate();
  const {channels} = useContext(EditCategoryContext);
  const userContext = useUserContext();
  const catalogChannel = userContext.get('catalogScope');
  const catalogLocale = userContext.get('catalogLocale');

  const [channel, setChannel] = useState(catalogChannel);
  const channelList = useMemo(() => Object.values(channels), [channels]);

  let selectedLocaleCode = catalogLocale;
  if (channels[channel]) {
    const selectedLocale = channels[channel]?.locales.find(locale => selectedLocaleCode === locale.code);
    selectedLocaleCode = selectedLocale ? selectedLocale.code : channels[channel]?.locales[0].code;
  }

  const [locale, setLocale] = useState(selectedLocaleCode);
  const handleLocaleChange = (value: string): void => {
    setLocale(value);
    userContext.set('catalogLocale', value, {});
    if (onLocaleChange) {
      onLocaleChange(value);
    }
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
        channel={{
          code: channel,
          label: getChannelTranslation(channelList, effectiveChannelCode, locale) ?? `[${channel}]`,
        }}
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
        {channels[channel] && (
          <LocaleSelector value={locale} values={channels[channel].locales} onChange={handleLocaleChange} />
        )}
      </SectionTitle>
      {attributeFields}
    </FormContainer>
  );
};
