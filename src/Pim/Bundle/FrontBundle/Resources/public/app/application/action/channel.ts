const fetcherRegistry = require('pim/fetcher-registry');
import ChannelInterface, {createChannel} from 'pimfront/app/domain/model/channel';
import {channelsUpdated} from 'pimfront/app/domain/event/channel';
import hidrateAll from 'pimfront/app/application/hidrator/hidrator';

const hidrator = (channel: any): ChannelInterface => {
  return createChannel(channel);
};

export const updateChannels = () => async (dispatch: any): Promise<void> => {
  const channels: ChannelInterface[] = await fetcherRegistry.getFetcher('channel').fetchAll();

  dispatch(channelsUpdated(hidrateAll<ChannelInterface>(hidrator)(channels)));
};
