import React from 'react';
import styled from 'styled-components';
import {Channel, ChannelCode, getLabel, LocaleCode} from '@akeneo-pim-community/shared';
import {Label} from 'akeneoassetmanager/application/component/app/label';

const ChannelLabelView = styled(Label)`
  margin-left: 10px;
`;

export const ChannelLabel = ({
  channelCode,
  locale,
  channels,
}: {
  channelCode: ChannelCode;
  locale: LocaleCode;
  channels: Channel[];
}) => {
  const channelLabel = getChannelLabel(channelCode, locale, channels);

  return <ChannelLabelView>{channelLabel}</ChannelLabelView>;
};

const getChannelLabel = (channelCode: ChannelCode, locale: LocaleCode, channels: Channel[]) => {
  const channel = channels.find((channel: Channel) => channel.code === channelCode);

  if (undefined === channel) return `[${channelCode}]`;

  return getLabel(channel.labels, locale, channelCode);
};
