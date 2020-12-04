import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from 'react-redux';
import {
  changeProductTabAction,
  endProductAttributesTabIsLoadedAction,
  showDataQualityInsightsAttributeToImproveAction,
  startProductAttributesTabIsLoadingAction,
} from '../../../infrastructure/reducer';
import {
  PRODUCT_ATTRIBUTES_TAB_NAME,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
  PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME,
  PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME,
} from '../../constant';
import {DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE} from './ProductContextListener';

interface TabEvent {
  currentTab: string;
}

interface ShowAttributeEvent {
  code: string;
}

interface LevelEvent {
  id: number;
  model_type: string;
}

interface PageContextListenerProps {}

export const PRODUCT_TAB_CHANGED = 'data-quality:product-tab:changed';
export const PRODUCT_ATTRIBUTES_TAB_LOADING = 'data-quality:product-attributes-tab:loading';
export const PRODUCT_ATTRIBUTES_TAB_LOADED = 'data-quality:product-attributes-tab:loaded';
export const PRODUCT_MODEL_LEVEL_CHANGED = 'data-quality:product-model-level:changed';

const PageContextListener: FunctionComponent<PageContextListenerProps> = () => {
  const dispatchAction = useDispatch();

  useEffect(() => {
    const handleProductTabChanged = (event: CustomEvent<TabEvent>) => {
      dispatchAction(changeProductTabAction(event.detail.currentTab));
      if (event.detail.currentTab !== PRODUCT_ATTRIBUTES_TAB_NAME) {
        dispatchAction(showDataQualityInsightsAttributeToImproveAction(null));
      }
    };
    const handleProductAttributesTabLoaded: EventListener = () => {
      dispatchAction(endProductAttributesTabIsLoadedAction());
    };
    const handleProductAttributesTabLoading: EventListener = () => {
      dispatchAction(startProductAttributesTabIsLoadingAction());
    };
    const handleShowAttribute = (event: CustomEvent<ShowAttributeEvent>) => {
      dispatchAction(showDataQualityInsightsAttributeToImproveAction(event.detail.code));
    };
    const handleProductModelLevelChanged = (event: CustomEvent<LevelEvent>) => {
      const {model_type} = event.detail;
      const currentTab = sessionStorage.getItem('current_column_tab') || PRODUCT_ATTRIBUTES_TAB_NAME;

      if (model_type === 'product_model' && currentTab === PRODUCT_ATTRIBUTES_TAB_NAME) {
        sessionStorage.setItem('current_column_tab', PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
        return;
      }

      if (model_type === 'product_model' && currentTab === PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME) {
        sessionStorage.setItem('current_column_tab', PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME);
        return;
      }

      if (model_type === 'product' && currentTab === PRODUCT_MODEL_ATTRIBUTES_TAB_NAME) {
        sessionStorage.setItem('current_column_tab', PRODUCT_ATTRIBUTES_TAB_NAME);
        return;
      }

      if (model_type === 'product' && currentTab === PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME) {
        sessionStorage.setItem('current_column_tab', PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME);
        return;
      }
    };

    window.addEventListener(PRODUCT_TAB_CHANGED, handleProductTabChanged as EventListener);
    window.addEventListener(PRODUCT_ATTRIBUTES_TAB_LOADED, handleProductAttributesTabLoaded);
    window.addEventListener(PRODUCT_ATTRIBUTES_TAB_LOADING, handleProductAttributesTabLoading);
    window.addEventListener(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, handleShowAttribute as EventListener);
    window.addEventListener(PRODUCT_MODEL_LEVEL_CHANGED, handleProductModelLevelChanged as EventListener);

    const currentTab = sessionStorage.getItem('current_column_tab') || PRODUCT_ATTRIBUTES_TAB_NAME;
    dispatchAction(changeProductTabAction(currentTab));
    dispatchAction(showDataQualityInsightsAttributeToImproveAction(null));

    return () => {
      window.removeEventListener(PRODUCT_TAB_CHANGED, handleProductTabChanged as EventListener);
      window.removeEventListener(PRODUCT_ATTRIBUTES_TAB_LOADED, handleProductAttributesTabLoaded);
      window.removeEventListener(PRODUCT_ATTRIBUTES_TAB_LOADING, handleProductAttributesTabLoading);
      window.removeEventListener(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, handleShowAttribute as EventListener);
      window.removeEventListener(PRODUCT_MODEL_LEVEL_CHANGED, handleProductModelLevelChanged as EventListener);
    };
  }, []);

  return <></>;
};

export default PageContextListener;
