import {Reducer} from 'redux';

export interface CatalogContextState {
  locale: string;
  channel: string;
}

interface UpdateCatalogContextAction {
  type: string;
  payload: CatalogContextState;
}

interface UpdateCatalogChannelAction {
  type: string;
  payload: Pick<CatalogContextState, 'channel'>;
}

interface UpdateCatalogLocaleAction {
  type: string;
  payload: Pick<CatalogContextState, 'locale'>;
}

export const CHANGE_CATALOG_CONTEXT_LOCALE = 'CHANGE_CATALOG_CONTEXT_LOCALE';
export const CHANGE_CATALOG_CONTEXT_CHANNEL = 'CHANGE_CATALOG_CONTEXT_CHANNEL';
export const INITIALIZE_CATALOG_CONTEXT = 'INITIALIZE_CATALOG_CONTEXT';

export const changeCatalogContextLocale = (locale: string): UpdateCatalogLocaleAction => {
  return {
    type: CHANGE_CATALOG_CONTEXT_LOCALE,
    payload: {
      locale: locale,
    },
  };
};

export const changeCatalogContextChannel = (channel: string): UpdateCatalogChannelAction => {
  return {
    type: CHANGE_CATALOG_CONTEXT_CHANNEL,
    payload: {
      channel: channel,
    },
  };
};

export const initializeCatalogContext = (channel: string, locale: string): UpdateCatalogContextAction => {
  return {
    type: INITIALIZE_CATALOG_CONTEXT,
    payload: {
      locale: locale,
      channel: channel,
    },
  };
};

const initialState: CatalogContextState = {
  locale: '',
  channel: '',
};

const catalogContextReducer: Reducer<CatalogContextState, UpdateCatalogContextAction> = (
  previousState = initialState,
  action
) => {
  switch (action.type) {
    case CHANGE_CATALOG_CONTEXT_CHANNEL:
      return {
        ...previousState,
        channel: action.payload.channel,
      };
    case CHANGE_CATALOG_CONTEXT_LOCALE:
      return {
        ...previousState,
        locale: action.payload.locale,
      };
    case INITIALIZE_CATALOG_CONTEXT:
      return {
        ...previousState,
        locale: action.payload.locale,
        channel: action.payload.channel,
      };
    default:
      return previousState;
  }
};
export default catalogContextReducer;
