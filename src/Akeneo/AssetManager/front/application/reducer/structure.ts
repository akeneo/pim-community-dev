import {combineReducers} from 'redux';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel, {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {arrayUnique} from 'akeneo-design-system';

export interface StructureState {
  locales: Locale[];
  channels: Channel[];
}

const locales = (state: Locale[] = [], {type, locales}: {type: string; locales: Locale[]}) => {
  switch (type) {
    case 'LOCALES_RECEIVED':
      state = locales;
      break;
    default:
      break;
  }

  return state;
};

const channels = (state: Channel[] = [], {type, channels}: {type: string; channels: Channel[]}) => {
  switch (type) {
    case 'CHANNELS_RECEIVED':
      state = channels;
      break;
    default:
      break;
  }

  return state;
};

export default combineReducers({
  locales,
  channels,
});

export const getLocales = (channels: Channel[], channelCode: string) => {
  const channel = channels.find((channel: Channel) => channel.code === channelCode);

  return undefined === channel ? [] : channel.locales;
};

const localeExist = (channelLocales: Locale[], locale: LocaleCode) => {
  return channelLocales.find(currentLocale => currentLocale.code === locale) !== undefined;
}

const getLocaleFromChannel = (channels: Channel[], channelCode: ChannelCode, localeCode: LocaleCode) => {
  const channelLocales = getLocales(channels, channelCode);

  return localeExist(channelLocales, localeCode) ? channelLocales[0].code : localeCode;
}

const getLocalesFromChannel = (channels: Channel[], channelCode: ChannelCode | null) => {
  if (null !== channelCode) {
    return getLocales(channels, channelCode)
  }

  return arrayUnique(
    channels.reduce((result, current) => {
      return [...result, ...current.locales];
    }, []),
    (first, second) => first.code === second.code
  );
}

export {getLocalesFromChannel, getLocaleFromChannel};
