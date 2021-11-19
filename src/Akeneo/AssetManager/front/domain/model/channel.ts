import {Channel, getLabel} from '@akeneo-pim-community/shared';
import Locale, {LocaleCode, denormalizeLocale} from 'akeneoassetmanager/domain/model/locale';
import {isString, isArray, isLabels} from 'akeneoassetmanager/domain/model/utils';

export const getChannelLabel = (channel: Channel, locale: LocaleCode) => {
  return getLabel(channel.labels, locale, channel.code);
};

export default Channel;

const isLocales = (locales: any): locales is Locale[] => {
  if (!isArray(locales)) {
    return false;
  }

  return !locales.some((locale: any) => {
    return !isString(locale.code) || !isString(locale.label) || !isString(locale.region) || !isString(locale.language);
  });
};

export const denormalizeChannel = (channel: any): Channel => {
  if (!isString(channel.code)) {
    throw new Error('Channel expects a string as code to be created');
  }

  if (!isLabels(channel.labels)) {
    throw new Error('Channel expects a label collection as labels to be created');
  }

  if (!isLocales(channel.locales)) {
    throw new Error('Channel expects an array as locales to be created');
  }

  const locales = channel.locales.map(denormalizeLocale);

  return {...channel, locales};
};
