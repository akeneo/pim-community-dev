import React from 'react';
import styled from 'styled-components';
import {
  filterErrors,
  getLocaleFromChannel,
  ChannelCode,
  LocaleCode,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../hooks';
import {Source} from '../../models';
import {ChannelDropdown} from './SourceConfigurator/ChannelDropdown';
import {LocaleDropdown} from './SourceConfigurator/LocaleDropdown';
import {Operations} from './SourceConfigurator/Operations';

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
  const channels = useChannels();
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const channelErrors = filterErrors(validationErrors, '[channel]');

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
          channel={source.channel}
          validationErrors={localeErrors}
          onChange={(localeCode: LocaleCode) => {
            onSourceChange({...source, locale: localeCode});
          }}
        />
      )}
      <Operations source={source} validationErrors={validationErrors} onSourceChange={onSourceChange} />
    </Container>
  );
};

export {SourceConfigurator};
