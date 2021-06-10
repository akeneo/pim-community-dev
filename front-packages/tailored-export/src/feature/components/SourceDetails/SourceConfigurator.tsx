import {Source} from '../../models';
import React from 'react';
import {getLocaleFromChannel, ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import {useChannels} from '../../hooks';
import styled from 'styled-components';
import {SourceDetailsPlaceholder} from './SourceDetailsPlaceholder';
import {ChannelDropdown} from './SourceConfigurator/ChannelDropdown';
import {LocaleDropdown} from './SourceConfigurator/LocaleDropdown';
import {Selector} from './SourceConfigurator/Selector/Selector';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

type SourceConfiguratorProps = {
  source: Source;
  onSourceChange: (updatedSource: Source) => void;
};

const SourceConfigurator = ({source, onSourceChange}: SourceConfiguratorProps) => {
  const channels = useChannels();

  return (
    <Container>
      {null !== source.channel && (
        <ChannelDropdown
          value={source.channel}
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
          onChange={(localeCode: LocaleCode) => {
            onSourceChange({...source, locale: localeCode});
          }}
        />
      )}
      <Selector
        source={source}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
      {'identifier' === source.code && <SourceDetailsPlaceholder />}
    </Container>
  );
};

export {SourceConfigurator};
