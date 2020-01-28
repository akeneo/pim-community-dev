import {Action, Reducer} from 'redux';
import {ATTRIBUTES_TAB_NAME} from "../../application/constant";

export interface PageContextState {
  currentTab: string;
  attributesTabIsLoading: boolean
}

type UpdatePageContextAction = UpdateTabContextAction & UpdateAttributesTabContextAction;

interface UpdateTabContextAction extends Action {
  payload: {
    currentTab: string;
  }
}

interface UpdateAttributesTabContextAction extends Action {}

export const CHANGE_PRODUCT_TAB = 'CHANGE_PRODUCT_TAB';
export const changeProductTabAction = (tabName: string): UpdatePageContextAction => {
  return {
    type: CHANGE_PRODUCT_TAB,
    payload: {
      currentTab: tabName
    }
  };
};

export const START_PRODUCT_ATTRIBUTES_TAB_LOADING = 'START_PRODUCT_ATTRIBUTES_TAB_LOADING';
export const startProductAttributesTabIsLoadingAction = (): UpdateAttributesTabContextAction => {
  return {
    type: START_PRODUCT_ATTRIBUTES_TAB_LOADING
  };
};

export const END_PRODUCT_ATTRIBUTES_TAB_LOADING = 'END_PRODUCT_ATTRIBUTES_TAB_LOADING';
export const endProductAttributesTabIsLoadedAction = (): UpdateAttributesTabContextAction => {
  return {
    type: END_PRODUCT_ATTRIBUTES_TAB_LOADING
  };
};

const initialState: PageContextState = {
  currentTab: ATTRIBUTES_TAB_NAME,
  attributesTabIsLoading: false,
};

const pageContextReducer: Reducer<PageContextState, UpdatePageContextAction> = (previousState = initialState, {type, payload}) => {
  switch(type) {
    case CHANGE_PRODUCT_TAB:
      return {
        ...previousState,
        currentTab: payload.currentTab
      };
    case START_PRODUCT_ATTRIBUTES_TAB_LOADING:
      return {
        ...previousState,
        attributesTabIsLoading: true
      };
    case END_PRODUCT_ATTRIBUTES_TAB_LOADING:
      return {
        ...previousState,
        attributesTabIsLoading: false
      };
    default:
      return previousState;
  }
};
export default pageContextReducer;
