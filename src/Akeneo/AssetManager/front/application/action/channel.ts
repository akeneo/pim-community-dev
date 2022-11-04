import {channelsReceived} from 'akeneoassetmanager/domain/event/channel';
import Channel from 'akeneoassetmanager/domain/model/channel';
import hydrator from 'akeneoassetmanager/application/hydrator/channel';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';

export const updateChannels = (channelFetcher: any): Promise<any> => {
  return new Promise((resolve: any, reject: any) => {
    channelFetcher
      .fetchAll({filter_locales: false})
      .then((backendChannels: any[]) => {
        const channels = hydrateAll<Channel>(hydrator)(backendChannels);

        resolve(channelsReceived(channels));
      })
      .fail(reject);
  });
};
