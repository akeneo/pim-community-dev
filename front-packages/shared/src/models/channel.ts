import {arrayUnique} from 'akeneo-design-system';
import {Locale, LocaleCode, isLocales, denormalizeLocale} from '../models';
import {isLabelCollection, LabelCollection, getLabel} from '../models';

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

const getAllLocales = (channels: Channel[]): Locale[] =>
  channels.reduce<Locale[]>(
    (locales, channel) => arrayUnique([...locales, ...channel.locales], (first, second) => first.code === second.code),
    []
  );

export {getChannelLabel, denormalizeChannel, getAllLocales};
export type {ChannelCode, Channel, ChannelReference};
