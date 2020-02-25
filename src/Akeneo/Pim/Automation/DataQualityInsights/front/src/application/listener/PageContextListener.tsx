import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from "react-redux";
import {
  changeProductTabAction,
  endProductAttributesTabIsLoadedAction, showDataQualityInsightsAttributeToImproveAction,
  startProductAttributesTabIsLoadingAction
} from "../../infrastructure/reducer";
import {ATTRIBUTES_TAB_NAME} from "../constant";
import {DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE} from "./ProductContextListener";

interface TabEvent {
  currentTab: string;
}

interface ShowAttributeEvent {
  code: string;
}

interface PageContextListenerProps {

}

export const PRODUCT_TAB_CHANGED = 'data-quality:product-tab:changed';
export const PRODUCT_ATTRIBUTES_TAB_LOADING = 'data-quality:product-attributes-tab:loading';
export const PRODUCT_ATTRIBUTES_TAB_LOADED = 'data-quality:product-attributes-tab:loaded';

const PageContextListener: FunctionComponent<PageContextListenerProps> = () => {
  const dispatchAction = useDispatch();

  useEffect(() => {
    const handleProductTabChanged = (event: CustomEvent<TabEvent>) => {
      dispatchAction(changeProductTabAction(event.detail.currentTab));
      if (event.detail.currentTab !== ATTRIBUTES_TAB_NAME) {
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

    window.addEventListener(PRODUCT_TAB_CHANGED, handleProductTabChanged as EventListener);
    window.addEventListener(PRODUCT_ATTRIBUTES_TAB_LOADED, handleProductAttributesTabLoaded);
    window.addEventListener(PRODUCT_ATTRIBUTES_TAB_LOADING, handleProductAttributesTabLoading);
    window.addEventListener(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, handleShowAttribute as EventListener);

    const currentTab = sessionStorage.getItem('current_column_tab') || ATTRIBUTES_TAB_NAME;
    dispatchAction(changeProductTabAction(currentTab));
    dispatchAction(showDataQualityInsightsAttributeToImproveAction(null));

    return (() => {
      window.removeEventListener(PRODUCT_TAB_CHANGED, handleProductTabChanged as EventListener);
      window.removeEventListener(PRODUCT_ATTRIBUTES_TAB_LOADED, handleProductAttributesTabLoaded);
      window.removeEventListener(PRODUCT_ATTRIBUTES_TAB_LOADING, handleProductAttributesTabLoading);
      window.removeEventListener(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, handleShowAttribute as EventListener);
    })
  }, []);

  return (
    <></>
  )
};

export default PageContextListener;
