import promisify from 'akeneoassetmanager/tools/promisify';
import {Channel} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {Locale} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import {isLabels} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {isString, isArray} from 'util';
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

const denormalizeChannel = (channel: any): Channel => {
  if (!isString(channel.code)) {
    throw Error('The code is not well formated');
  }

  if (!isLabels(channel.labels)) {
    throw Error('The code is not well formated');
  }

  if (!isLocales(channel.locales)) {
    throw Error('The code is not well formated');
  }

  return channel;
};

const isLocales = (locales: any): locales is Locale[] => {
  if (!isArray(locales)) {
    return false;
  }

  return !locales.some((locale: any) => {
    return !isString(locale.code) || !isString(locale.label) || !isString(locale.region) || !isString(locale.language);
  });
};
