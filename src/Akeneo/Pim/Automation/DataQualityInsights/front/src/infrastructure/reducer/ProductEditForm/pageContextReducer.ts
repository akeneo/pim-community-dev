import {Action, Reducer} from 'redux';
import {PRODUCT_ATTRIBUTES_TAB_NAME} from '../../../application/constant';
import {ProductEditFormPageContextState} from '../../../application/state/PageContextState';

type UpdatePageContextAction = UpdateTabContextAction &
  UpdateAttributesTabContextAction &
  UpdateAttributeToImproveContextAction;

interface UpdateTabContextAction extends Action {
  payload: {
    currentTab: string;
  };
}

interface UpdateAttributesTabContextAction extends Action {}

interface UpdateAttributeToImproveContextAction extends Action {
  payload: {
    attributeToImprove: string | null;
  };
}

interface ProductSavingContextAction extends Action {}

export const CHANGE_PRODUCT_TAB = 'CHANGE_PRODUCT_TAB';
export const changeProductTabAction = (tabName: string): UpdateTabContextAction => {
  return {
    type: CHANGE_PRODUCT_TAB,
    payload: {
      currentTab: tabName,
    },
  };
};

export const START_PRODUCT_ATTRIBUTES_TAB_LOADING = 'START_PRODUCT_ATTRIBUTES_TAB_LOADING';
export const startProductAttributesTabIsLoadingAction = (): UpdateAttributesTabContextAction => {
  return {
    type: START_PRODUCT_ATTRIBUTES_TAB_LOADING,
  };
};

export const END_PRODUCT_ATTRIBUTES_TAB_LOADING = 'END_PRODUCT_ATTRIBUTES_TAB_LOADING';
export const endProductAttributesTabIsLoadedAction = (): UpdateAttributesTabContextAction => {
  return {
    type: END_PRODUCT_ATTRIBUTES_TAB_LOADING,
  };
};

export const SHOW_DATA_QUALITY_INSIGHTS_ATTRIBUTE_TO_IMPROVE = 'SHOW_DATA_QUALITY_INSIGHTS_ATTRIBUTE_TO_IMPROVE';
export const showDataQualityInsightsAttributeToImproveAction = (
  attributeCode: string | null
): UpdateAttributeToImproveContextAction => {
  return {
    type: SHOW_DATA_QUALITY_INSIGHTS_ATTRIBUTE_TO_IMPROVE,
    payload: {
      attributeToImprove: attributeCode,
    },
  };
};

export const START_PRODUCT_SAVING = 'START_PRODUCT_SAVING';
export const startProductSavingAction = (): ProductSavingContextAction => {
  return {
    type: START_PRODUCT_SAVING,
  };
};

export const END_PRODUCT_SAVING = 'END_PRODUCT_SAVING';
export const endProductSavingAction = (): ProductSavingContextAction => {
  return {
    type: END_PRODUCT_SAVING,
  };
};

const initialState: ProductEditFormPageContextState = {
  currentTab: PRODUCT_ATTRIBUTES_TAB_NAME,
  attributesTabIsLoading: false,
  attributeToImprove: null,
  isProductSaving: false,
};

const pageContextReducer: Reducer<ProductEditFormPageContextState, UpdatePageContextAction> = (
  previousState = initialState,
  {type, payload}
) => {
  switch (type) {
    case CHANGE_PRODUCT_TAB:
      return {
        ...previousState,
        currentTab: payload.currentTab,
      };
    case START_PRODUCT_ATTRIBUTES_TAB_LOADING:
      return {
        ...previousState,
        attributesTabIsLoading: true,
      };
    case END_PRODUCT_ATTRIBUTES_TAB_LOADING:
      return {
        ...previousState,
        attributesTabIsLoading: false,
      };
    case SHOW_DATA_QUALITY_INSIGHTS_ATTRIBUTE_TO_IMPROVE:
      return {
        ...previousState,
        attributeToImprove: payload.attributeToImprove,
      };
    case START_PRODUCT_SAVING:
      return {
        ...previousState,
        isProductSaving: true,
      };
    case END_PRODUCT_SAVING:
      return {
        ...previousState,
        isProductSaving: false,
      };
    default:
      return previousState;
  }
};
export default pageContextReducer;
