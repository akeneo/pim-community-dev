import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from "react-redux";
import {
  changeProductTabAction,
  endProductAttributesTabIsLoadedAction,
  startProductAttributesTabIsLoadingAction
} from "../../infrastructure/reducer";
import {ATTRIBUTES_TAB_NAME} from "../constant";

interface TabEvent {
  currentTab: string;
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
    };
    const handleProductAttributesTabLoaded: EventListener = () => {
      dispatchAction(endProductAttributesTabIsLoadedAction());
    };
    const handleProductAttributesTabLoading: EventListener = () => {
      dispatchAction(startProductAttributesTabIsLoadingAction());
    };

    window.addEventListener(PRODUCT_TAB_CHANGED, handleProductTabChanged as EventListener);
    window.addEventListener(PRODUCT_ATTRIBUTES_TAB_LOADED, handleProductAttributesTabLoaded);
    window.addEventListener(PRODUCT_ATTRIBUTES_TAB_LOADING, handleProductAttributesTabLoading);

    const currentTab = sessionStorage.getItem('current_column_tab') || ATTRIBUTES_TAB_NAME;
    dispatchAction(changeProductTabAction(currentTab));

    return (() => {
      window.removeEventListener(PRODUCT_TAB_CHANGED, handleProductTabChanged as EventListener);
      window.removeEventListener(PRODUCT_ATTRIBUTES_TAB_LOADED, handleProductAttributesTabLoaded);
      window.removeEventListener(PRODUCT_ATTRIBUTES_TAB_LOADING, handleProductAttributesTabLoading);
    })
  }, []);

  return (
    <></>
  )
};

export default PageContextListener;
