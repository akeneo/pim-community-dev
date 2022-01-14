import React from 'react';
import styled from 'styled-components';
import {filterErrors, getLocaleFromChannel, getLocalesFromChannel} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {isQualityScoreSource} from './model';
import {InvalidPropertySourceError} from '../error';
import {ChannelDropdown} from '../../../components/shared/ChannelDropdown';
import {LocaleDropdown} from '../../shared/LocaleDropdown';
import {useChannels} from '../../../hooks';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
`;

const QualityScoreConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  if (!isQualityScoreSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for Quality Score configurator`);
  }

  const channels = useChannels();
  const locales = getLocalesFromChannel(channels, source.channel);
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const localeErrors = filterErrors(validationErrors, '[locale]');

  return (
    <Container>
      <ChannelDropdown
        channels={channels}
        value={source.channel}
        validationErrors={channelErrors}
        onChange={channel => {
          const localeReference = getLocaleFromChannel(channels, channel, source.locale);

          if (null !== localeReference) {
            onSourceChange({...source, channel, locale: localeReference});
          }
        }}
      />
      <LocaleDropdown
        value={source.locale}
        validationErrors={localeErrors}
        locales={locales}
        onChange={locale => onSourceChange({...source, locale})}
      />
    </Container>
  );
};

export {QualityScoreConfigurator};
