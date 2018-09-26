import ChannelFetcher from 'akeneoenrichedentity/domain/fetcher/channel';
import Channel from 'akeneoenrichedentity/domain/model/channel';
import hydrator from 'akeneoenrichedentity/application/hydrator/channel';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';
import errorHandler from 'akeneoenrichedentity/infrastructure/tools/error-handler';

const routing = require('routing');

let Channels: Channel[] | null = null;
export class ChannelFetcherImplementation implements ChannelFetcher {
  constructor(private hydrator: (backendChannel: any) => Channel) {
    Object.freeze(this);
  }

  async fetchAll(): Promise<Channel[]> {
    if (null === Channels) {
      const backendChannels = await getJSON(routing.generate('pim_enrich_channel_rest_index')).catch(errorHandler);

      Channels = hydrateAll<Channel>(this.hydrator)(backendChannels);
    }

    return Channels;
  }
}

export default new ChannelFetcherImplementation(hydrator);
