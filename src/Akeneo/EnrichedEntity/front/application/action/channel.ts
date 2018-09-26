import {channelsReceived} from 'akeneoenrichedentity/domain/event/channel';
import channelFetcher from 'akeneoenrichedentity/infrastructure/fetcher/channel';

export const updateChannels = () => async (dispatch: any): Promise<void> => {
  const channels = await channelFetcher.fetchAll();

  dispatch(channelsReceived(channels));
};
