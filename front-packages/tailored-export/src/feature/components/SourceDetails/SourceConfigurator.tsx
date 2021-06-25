import React from 'react';
import styled from 'styled-components';
import {
  filterErrors,
  ChannelCode,
  LocaleCode,
  ValidationError,
  getLocalesFromChannel,
  getLocaleFromChannel,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {useAttribute, useChannels} from '../../hooks';
import {Source} from '../../models';
import {ChannelDropdown} from './SourceConfigurator/ChannelDropdown';
import {Operations} from './SourceConfigurator/Operations';
import {LocaleDropdown} from './SourceConfigurator/LocaleDropdown';
import {Helper} from 'akeneo-design-system';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

type SourceConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

const SourceConfigurator = ({source, validationErrors, onSourceChange}: SourceConfiguratorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const locales = getLocalesFromChannel(channels, source.channel);
  const attribute = useAttribute(source.code);
  const localeSpecificFilteredLocales =
    attribute && attribute.is_locale_specific
      ? locales.filter(({code}) => attribute.available_locales.includes(code))
      : locales;

  return (
    <Container>
      {null !== source.channel && (
        <ChannelDropdown
          value={source.channel}
          channels={channels}
          validationErrors={channelErrors}
          onChange={(channelCode: ChannelCode) => {
            const localeCode = getLocaleFromChannel(channels, channelCode, source.locale);
            onSourceChange({...source, locale: localeCode, channel: channelCode});
          }}
        />
      )}
      {null !== source.locale && (
        <LocaleDropdown
          value={source.locale}
          validationErrors={localeErrors}
          locales={localeSpecificFilteredLocales}
          onChange={(localeCode: LocaleCode) => {
            onSourceChange({...source, locale: localeCode});
          }}
        >
          {attribute && attribute.is_locale_specific && (
            <Helper inline>{translate('akeneo.tailored_export.column_details.sources.locale_specific.info')}</Helper>
          )}
        </LocaleDropdown>
      )}
      <Operations source={source} validationErrors={validationErrors} onSourceChange={onSourceChange} />
    </Container>
  );
};

export {SourceConfigurator};
