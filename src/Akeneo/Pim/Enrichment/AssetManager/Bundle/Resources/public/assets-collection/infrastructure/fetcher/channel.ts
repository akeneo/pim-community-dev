import promisify from 'akeneoassetmanager/tools/promisify';
import {Channel} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
const fetcherRegistry = require('pim/fetcher-registry');

export const fetchChannels = async (): Promise<Channel[]> => {
  return promisify(fetcherRegistry.getFetcher('channel').fetchAll());
};
