import React from 'react';
import {Channel} from '@akeneo-pim-community/shared';

export type ChannelFetcher = {
  fetchAll: () => Promise<Channel[]>;
};

export const useChannels = (channelFetcher: ChannelFetcher) => {
  const [channels, setChannels] = React.useState<Channel[]>([]);

  React.useEffect(() => {
    channelFetcher.fetchAll().then(setChannels);
  }, []);

  return channels;
};
