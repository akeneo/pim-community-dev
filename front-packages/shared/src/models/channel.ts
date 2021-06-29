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

const getChannelLabel = (channel: Channel, locale: LocaleCode) => {
  return getLabel(channel.labels, locale, channel.code);
};

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

const getLocaleFromChannel = (channels: Channel[], channelCode: ChannelCode, localeCode: LocaleReference) => {
  if (null === localeCode) return null;
  const channelLocales = getLocales(channels, channelCode);

  return !localeExists(channelLocales, localeCode) ? channelLocales[0].code : localeCode;
};

const getLocalesFromChannel = (channels: Channel[], channelCode: ChannelCode | null) => {
  if (null !== channelCode) {
    return getLocales(channels, channelCode);
  }

  return getAllLocalesFromChannels(channels);
};

const getLocales = (channels: Channel[], channelCode: string) => {
  const channel = channels.find((channel: Channel) => channel.code === channelCode);

  return undefined === channel ? [] : channel.locales;
};

export {getChannelLabel, denormalizeChannel, getAllLocalesFromChannels, getLocaleFromChannel, getLocalesFromChannel};
export type {ChannelCode, Channel, ChannelReference};
