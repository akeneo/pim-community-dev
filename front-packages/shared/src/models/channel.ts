import {arrayUnique} from 'akeneo-design-system';
import {
  Locale,
  LocaleCode,
  isLocales,
  denormalizeLocale,
  localeExists,
  LocaleReference,
  isLabelCollection,
  LabelCollection,
  getLabel,
} from '../models';

type ChannelCode = string;
type ChannelReference = ChannelCode | null;

type Channel = {
  code: ChannelCode;
  labels: LabelCollection;
  locales: Locale[];
  category_tree: string;
  conversion_units: any[];
  currencies: string[];
  meta: {
    created: any;
    form: string;
    id: number;
    updated: any;
  };
};

const getChannelLabel = (channel: Channel, locale: LocaleCode) => getLabel(channel.labels, locale, channel.code);

const denormalizeChannel = (channel: any): Channel => {
  if ('string' !== typeof channel.code) {
    throw new Error('Channel expects a string as code to be created');
  }

  if (!isLabelCollection(channel.labels)) {
    throw new Error('Channel expects a label collection as labels to be created');
  }

  if (!isLocales(channel.locales)) {
    throw new Error('Channel expects an array as locales to be created');
  }

  const locales = channel.locales.map(denormalizeLocale);

  return {...channel, locales};
};

const getAllLocalesFromChannels = (channels: Channel[]): Locale[] =>
  channels.reduce<Locale[]>(
    (locales, channel) => arrayUnique([...locales, ...channel.locales], (first, second) => first.code === second.code),
    []
  );

const getLocaleFromChannel = (channels: Channel[], channelCode: ChannelCode, localeReference: LocaleReference) => {
  if (null === localeReference) return null;
  const channelLocales = getLocales(channels, channelCode);

  return !localeExists(channelLocales, localeReference) ? channelLocales[0].code : localeReference;
};

const getLocalesFromChannel = (channels: Channel[], channelReference: ChannelReference) =>
  null === channelReference ? getAllLocalesFromChannels(channels) : getLocales(channels, channelReference);

const getLocales = (channels: Channel[], channelCode: ChannelCode) => {
  const channel = channels.find(({code}) => code === channelCode);

  return undefined === channel ? [] : channel.locales;
};

const getCurrencyCodesFromChannelReference = (channels: Channel[], channelReference: ChannelReference): string[] =>
  null === channelReference
    ? getAllCurrencyCodesFromChannels(channels)
    : getCurrencyCodesFromChannel(channels, channelReference);

const getAllCurrencyCodesFromChannels = (channels: Channel[]): string[] => {
  return channels.reduce<string[]>((currencies, channel) => arrayUnique([...currencies, ...channel.currencies]), []);
};

const getCurrencyCodesFromChannel = (channels: Channel[], channelCode: ChannelCode): string[] => {
  const channel = channels.find(({code}) => code === channelCode);

  return undefined === channel ? [] : channel.currencies;
};

export {
  getChannelLabel,
  denormalizeChannel,
  getAllLocalesFromChannels,
  getLocaleFromChannel,
  getLocalesFromChannel,
  getCurrencyCodesFromChannelReference,
};
export type {ChannelCode, Channel, ChannelReference};
