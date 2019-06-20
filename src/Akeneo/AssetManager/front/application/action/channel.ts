import {channelsReceived} from 'akeneoassetmanager/domain/event/channel';
import Channel from 'akeneoassetmanager/domain/model/channel';
import hydrator from 'akeneoassetmanager/application/hydrator/channel';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';
const fetcherRegistry = require('pim/fetcher-registry');

export const updateChannels = () => async (dispatch: any): Promise<any> => {
  return new Promise((resolve: any, reject: any) => {
    fetcherRegistry
      .getFetcher('channel')
      .fetchAll({filter_locales: false})
      .then((backendChannels: any[]) => {
        const channels = hydrateAll<Channel>(hydrator)(backendChannels);

        dispatch(channelsReceived(channels));
        resolve();
      })
      .fail(reject);
  });
};
