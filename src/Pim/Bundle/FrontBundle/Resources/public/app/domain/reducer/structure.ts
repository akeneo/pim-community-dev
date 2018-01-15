import { combineReducers } from 'redux'
import Locale from 'pimfront/app/domain/model/locale';

export interface StructureState {
  locales: Locale[];
};

const locales = (state: Locale[] = [], {type, locales}: {type: string, locales: Locale[]}) => {
  switch (type) {
    case 'LOCALES_UPDATED':
      state = locales;
    break;
    default:
    break;
  }

  return state;
};

export default combineReducers({
  locales
});
