import React from 'react';
import styled from 'styled-components';
import {ArrowIcon, getColor, Helper, Locale, SelectInput} from 'akeneo-design-system';
import {
  Channel,
  ChannelCode,
  filterErrors,
  getAllLocalesFromChannels,
  getLabel,
  getLocaleFromChannel,
  LocaleCode,
  useTranslate,
  useUserContext,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {ReferenceEntityAttribute} from '../../../../models';
import {ReferenceEntityAttributeSelection} from '../model';
import {ReferenceEntityCollectionAttributeSelection} from '../../ReferenceEntityCollection/model';

const SubField = styled.div`
  display: flex;
  gap: 5px;
  align-items: baseline;
  margin-left: 8px;
  color: ${getColor('grey', 100)};
`;

const InnerField = styled.div`
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  gap: 5px;
`;

type AttributeSelection = ReferenceEntityAttributeSelection | ReferenceEntityCollectionAttributeSelection;

type AttributeSelectorProps<SelectionType extends AttributeSelection> = {
  attribute: ReferenceEntityAttribute;
  selection: SelectionType;
  validationErrors: ValidationError[];
  channels: Channel[];
  onSelectionChange: (selection: SelectionType) => void;
};

const AttributeSelector = <SelectionType extends AttributeSelection>({
  attribute,
  selection,
  channels,
  validationErrors,
  onSelectionChange,
}: AttributeSelectorProps<SelectionType>) => {
  const translate = useTranslate();
  const catalogLocale = useUserContext().get('catalogLocale');
  const locales = getAllLocalesFromChannels(channels);
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const localeErrors = filterErrors(validationErrors, '[locale]');

  const handleChannelChange = (channel: ChannelCode) => {
    const locale = getLocaleFromChannel(channels, channel, selection.locale);
    onSelectionChange({...selection, locale, channel});
  };

  const handleLocaleChange = (locale: LocaleCode) => onSelectionChange({...selection, locale});

  return (
    <>
      {attribute.value_per_channel && null !== selection.channel && (
        <SubField>
          <ArrowIcon />
          <InnerField>
            <SelectInput
              invalid={0 < channelErrors.length}
              clearable={false}
              emptyResultLabel={translate('pim_common.no_result')}
              openLabel={translate('pim_common.open')}
              value={selection.channel}
              onChange={handleChannelChange}
            >
              {channels.map(channel => (
                <SelectInput.Option
                  key={channel.code}
                  title={getLabel(channel.labels, catalogLocale, channel.code)}
                  value={channel.code}
                >
                  {getLabel(channel.labels, catalogLocale, channel.code)}
                </SelectInput.Option>
              ))}
            </SelectInput>
            {channelErrors.map((error, index) => (
              <Helper key={index} inline={true} level="error">
                {translate(error.messageTemplate, error.parameters)}
              </Helper>
            ))}
          </InnerField>
        </SubField>
      )}
      {attribute.value_per_locale && null !== selection.locale && (
        <SubField>
          <ArrowIcon />
          <InnerField>
            <SelectInput
              invalid={0 < localeErrors.length}
              clearable={false}
              emptyResultLabel={translate('pim_common.no_result')}
              openLabel={translate('pim_common.open')}
              value={selection.locale}
              onChange={handleLocaleChange}
            >
              {locales.map(locale => (
                <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                  <Locale code={locale.code} languageLabel={locale.label} />
                </SelectInput.Option>
              ))}
            </SelectInput>
            {localeErrors.map((error, index) => (
              <Helper key={index} inline={true} level="error">
                {translate(error.messageTemplate, error.parameters)}
              </Helper>
            ))}
          </InnerField>
        </SubField>
      )}
    </>
  );
};

export {AttributeSelector};
