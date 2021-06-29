import {Reducer} from 'redux';

export interface CatalogContextState {
  locale: string | undefined;
  channel: string | undefined;
}

interface UpdateCatalogContextAction {
  type: string;
  payload: {
    locale?: string;
    channel?: string;
  };
}
export const CHANGE_CATALOG_CONTEXT_LOCALE = 'CHANGE_CATALOG_CONTEXT_LOCALE';
export const CHANGE_CATALOG_CONTEXT_CHANNEL = 'CHANGE_CATALOG_CONTEXT_CHANNEL';
export const INITIALIZE_CATALOG_CONTEXT = 'INITIALIZE_CATALOG_CONTEXT';

export const changeCatalogContextLocale = (locale: string): UpdateCatalogContextAction => {
  return {
    type: CHANGE_CATALOG_CONTEXT_LOCALE,
    payload: {
      locale: locale,
    },
  };
};

export const changeCatalogContextChannel = (channel: string): UpdateCatalogContextAction => {
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
  locale: undefined,
  channel: undefined,
};

const catalogContextReducer: Reducer<CatalogContextState, UpdateCatalogContextAction> = (
  previousState = initialState,
  {type, payload}
) => {
  switch (type) {
    case CHANGE_CATALOG_CONTEXT_CHANNEL:
      return {
        ...previousState,
        channel: payload.channel,
      };
    case CHANGE_CATALOG_CONTEXT_LOCALE:
      return {
        ...previousState,
        locale: payload.locale,
      };
    case INITIALIZE_CATALOG_CONTEXT:
      return {
        ...previousState,
        locale: payload.locale,
        channel: payload.channel,
      };
    default:
      return previousState;
  }
};
export default catalogContextReducer;
