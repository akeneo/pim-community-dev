import {channelsReceived} from 'akeneoreferenceentity/domain/event/channel';
import Channel from 'akeneoreferenceentity/domain/model/channel';
import hydrator from 'akeneoreferenceentity/application/hydrator/channel';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
const fetcherRegistry = require('pim/fetcher-registry');

export const updateChannels = () => async (dispatch: any): Promise<void> => {
  fetcherRegistry
    .getFetcher('channel')
    .fetchAll()
    .then((backendChannels: any[]) => {
      const channels = hydrateAll<Channel>(hydrator)(backendChannels);

      dispatch(channelsReceived(channels));
    });
};
