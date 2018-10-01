import {channelsReceived} from 'akeneoreferenceentity/domain/event/channel';
import channelFetcher from 'akeneoreferenceentity/infrastructure/fetcher/channel';

export const updateChannels = () => async (dispatch: any): Promise<void> => {
  const channels = await channelFetcher.fetchAll();

  dispatch(channelsReceived(channels));
};
