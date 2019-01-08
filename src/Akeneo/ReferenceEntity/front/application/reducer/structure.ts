import {combineReducers} from 'redux';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import Channel from 'akeneoreferenceentity/domain/model/channel';

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

export const getCatalogLocale = (channels: Channel[], channelCode: string, localeCode: string) => {
  const channel = channels.find((channel: Channel) => channel.code === channelCode);

  if (undefined === channel || channel.locales.map((locale: Locale) => locale.code).includes(localeCode)) {
    return localeCode;
  }

  return channel.locales[0].code;
};

export const getLocales = (channels: Channel[], channelCode: string) => {
  const channel = channels.find((channel: Channel) => channel.code === channelCode);

  return undefined === channel ? [] : channel.locales;
};
