import {getLabel} from 'pimui/js/i18n';
import {Locale, LocaleCode, isLocales, denormalizeLocale} from 'pimui/common/model/locale';
import {isLabelCollection, LabelCollection} from 'pimui/common/model/label-collection';

type ChannelCode = string;

type Channel = {
  code: ChannelCode;
  labels: LabelCollection;
  locales: Locale[];
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

export {ChannelCode, Channel, getChannelLabel, denormalizeChannel};
