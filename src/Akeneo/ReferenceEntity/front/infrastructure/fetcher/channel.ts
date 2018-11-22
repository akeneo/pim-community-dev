import ChannelFetcher from 'akeneoreferenceentity/domain/fetcher/channel';
import Channel from 'akeneoreferenceentity/domain/model/channel';
import hydrator from 'akeneoreferenceentity/application/hydrator/channel';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoreferenceentity/tools/fetch';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';

const routing = require('routing');

let Channels: Channel[] | null = null;
export class ChannelFetcherImplementation implements ChannelFetcher {
  async fetchAll(): Promise<Channel[]> {
    if (null === Channels) {
      const backendChannels = await getJSON(routing.generate('pim_enrich_channel_rest_index')).catch(errorHandler);

      Channels = hydrateAll<Channel>(hydrator)(backendChannels);
    }

    return Channels;
  }
}

export default new ChannelFetcherImplementation();
