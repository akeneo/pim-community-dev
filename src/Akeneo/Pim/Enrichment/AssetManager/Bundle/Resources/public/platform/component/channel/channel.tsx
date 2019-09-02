import * as React from 'react';
import {ChannelCode, Channel} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import styled from 'styled-components';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';

const ChannelLabelView = styled(Label)`
  margin-left: 10px;
`;

export const ChannelLabel = ({channelCode, locale, channels}: {channelCode: ChannelCode, locale: LocaleCode, channels: Channel[]}) => {
  const channelLabel = getChannelLabel(channelCode, locale, channels);

  return (
    <ChannelLabelView>
      {channelLabel}
    </ChannelLabelView>
  )
}

const getChannelLabel = (channelCode: ChannelCode, locale: LocaleCode, channels: Channel[]) => {
  const channel = channels.find((channel: Channel) => channel.code === channelCode);

  if (undefined === channel) return `[${channelCode}]`;

  return getLabel(channel.labels, locale, channelCode)
}
