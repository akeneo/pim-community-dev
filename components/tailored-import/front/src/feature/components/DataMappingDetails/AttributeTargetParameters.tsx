import React from 'react';
import {Helper, SectionTitle} from 'akeneo-design-system';
import {
  ChannelCode,
  getLocaleFromChannel,
  getLocalesFromChannel,
  LocaleCode,
  Section,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {AttributeTarget} from '../../models';
import {useAttribute, useChannels} from '../../hooks';
import {ChannelDropdown} from './ChannelDropdown';
import {LocaleDropdown} from './LocaleDropdown';

type AttributeTargetParametersProps = {
  target: AttributeTarget;
  onTargetChange: (target: AttributeTarget) => void;
};

const AttributeTargetParameters = ({target, onTargetChange}: AttributeTargetParametersProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const [, attribute] = useAttribute(target.code);
  const locales = getLocalesFromChannel(channels, target.channel);
  const localeSpecificFilteredLocales =
    null !== attribute && attribute.is_locale_specific
      ? locales.filter(({code}) => attribute.available_locales.includes(code))
      : locales;

  const handleChannelChange = (channel: ChannelCode) => {
    const locale = getLocaleFromChannel(channels, channel, target.locale);
    onTargetChange({...target, channel, locale});
  };

  const handleLocaleChange = (locale: LocaleCode) => {
    onTargetChange({...target, locale});
  };

  return (
    <Section>
      <SectionTitle>
        <SectionTitle.Title>
          {translate('akeneo.tailored_import.data_mapping.target.target_parameters')}
        </SectionTitle.Title>
      </SectionTitle>
      {0 < channels.length && null !== target.channel && (
        <ChannelDropdown
          value={target.channel}
          channels={channels}
          validationErrors={[]}
          onChange={handleChannelChange}
        />
      )}
      {0 < localeSpecificFilteredLocales.length && null !== target.locale && (
        <LocaleDropdown
          value={target.locale}
          validationErrors={[]}
          locales={localeSpecificFilteredLocales}
          onChange={handleLocaleChange}
        >
          {attribute?.is_locale_specific && (
            <Helper inline={true}>{translate('akeneo.tailored_import.data_mapping.target.locale_specific')}</Helper>
          )}
        </LocaleDropdown>
      )}
    </Section>
  );
};

export {AttributeTargetParameters};
