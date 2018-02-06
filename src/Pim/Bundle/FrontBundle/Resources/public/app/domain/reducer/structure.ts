import {combineReducers} from 'redux';
import Locale from 'pimfront/app/domain/model/locale';
import Channel from 'pimfront/app/domain/model/channel';

export interface StructureState {
  locales: Locale[];
  channels: Channel[];
}

const locales = (state: Locale[] = [], {type, locales}: {type: string; locales: Locale[]}) => {
  switch (type) {
    case 'LOCALES_UPDATED':
      state = locales;
      break;
    default:
      break;
  }

  return state;
};

const channels = (state: Channel[] = [], {type, channels}: {type: string; channels: Channel[]}) => {
  switch (type) {
    case 'CHANNELS_UPDATED':
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
