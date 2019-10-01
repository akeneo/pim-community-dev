import promisify from 'akeneoassetmanager/tools/promisify';
import {isArray} from 'akeneoassetmanager/domain/model/utils';
import Channel, {denormalizeChannel} from 'akeneoassetmanager/domain/model/channel';
const fetcherRegistry = require('pim/fetcher-registry');

/**
 * Need to export this function in a variable to be able to mock it in our tests.
 * We couldn't require the pim/fetcher-registry in our test stack. We need to mock the legacy fetcher used.
 */
export const channelFetcher = () => fetcherRegistry.getFetcher('channel');
export const fetchChannels = (channelFetcher: any) => async (): Promise<Channel[]> => {
  const channels = await promisify(channelFetcher.fetchAll());

  return denormalizeChannelCollection(channels);
};

const denormalizeChannelCollection = (channels: any): Channel[] => {
  if (!isArray(channels)) {
    throw Error('not a valid channel collection');
  }

  return channels.map((channel: any) => denormalizeChannel(channel));
};
