import promisify from 'akeneoassetmanager/tools/promisify';
import {Channel} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {validateLabels} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
const fetcherRegistry = require('pim/fetcher-registry');

export const fetchChannels = async (): Promise<Channel[]> => {
  const channels = await promisify(fetcherRegistry.getFetcher('channel').fetchAll());

  return denormalizeChannelsCollection(channels);
};

const denormalizeChannelsCollection = (channels: any): Channel[] => {
  if (!Array.isArray(channels)) {
    throw Error('not a valid channel collection');
  }

  return channels.map((channel: any) => denormalizeChannel(channel));
};

const denormalizeChannel = (channel: any): Channel => {
  if (undefined === channel.code || typeof channel.code !== 'string') {
    throw Error('The code is not well formated');
  }

  if (undefined === channel.labels || !validateLabels(channel.labels)) {
    throw Error('The code is not well formated');
  }

  if (undefined === channel.locales || !validateLocales(channel.locale)) {
    throw Error('The code is not well formated');
  }

  return channel;
};

const validateLocales = (locale: any): boolean => {
  if (typeof locale !== 'object') {
    return false;
  }

  if (undefined === locale.code || typeof locale.code !== 'string') {
    return false;
  }

  if (undefined === locale.label || typeof locale.label !== 'string') {
    return false;
  }

  if (undefined === locale.region || typeof locale.region !== 'string') {
    return false;
  }

  if (undefined === locale.language || typeof locale.language !== 'string') {
    return false;
  }

  return true;
};
