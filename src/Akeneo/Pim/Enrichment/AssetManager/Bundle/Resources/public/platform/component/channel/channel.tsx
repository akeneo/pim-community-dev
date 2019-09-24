import * as React from 'react';
import {getLabel} from 'pimui/js/i18n';
import styled from 'styled-components';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel, {ChannelCode} from 'akeneoassetmanager/domain/model/channel';

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
