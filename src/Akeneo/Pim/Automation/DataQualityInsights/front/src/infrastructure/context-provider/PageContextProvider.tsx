import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from "react-redux";
import {
  changeProductTabAction,
  endProductAttributesTabIsLoadedAction,
  startProductAttributesTabIsLoadingAction
} from "../reducer";
import {ATTRIBUTES_TAB_NAME} from "../../application/constant";

interface TabEvent {
  currentTab: string;
}

interface PageContextProviderProps {

}

export const PRODUCT_TAB_CHANGED = 'data-quality:product-tab:changed';
export const PRODUCT_ATTRIBUTES_TAB_LOADING = 'data-quality:product-attributes-tab:loading';
export const PRODUCT_ATTRIBUTES_TAB_LOADED = 'data-quality:product-attributes-tab:loaded';

const PageContextProvider: FunctionComponent<PageContextProviderProps> = () => {
  const dispatchAction = useDispatch();

  useEffect(() => {
    window.addEventListener(PRODUCT_TAB_CHANGED, ((event: CustomEvent<TabEvent>) => {
      dispatchAction(changeProductTabAction(event.detail.currentTab));
    }) as EventListener);

    const currentTab = sessionStorage.getItem('current_column_tab') || ATTRIBUTES_TAB_NAME;
    dispatchAction(changeProductTabAction(currentTab));
  }, []);

  useEffect(() => {
    const handleProductAttributesTabLoaded: EventListener = () => {
      dispatchAction(endProductAttributesTabIsLoadedAction());
    };
    const handleProductAttributesTabLoading: EventListener = () => {
      dispatchAction(startProductAttributesTabIsLoadingAction());
    };

    window.addEventListener(PRODUCT_ATTRIBUTES_TAB_LOADED, handleProductAttributesTabLoaded);
    window.addEventListener(PRODUCT_ATTRIBUTES_TAB_LOADING, handleProductAttributesTabLoading);

    return (() => {
      window.removeEventListener(PRODUCT_ATTRIBUTES_TAB_LOADED, handleProductAttributesTabLoaded);
      window.removeEventListener(PRODUCT_ATTRIBUTES_TAB_LOADING, handleProductAttributesTabLoading);
    })
  }, []);

  return (
    <></>
  )
};

export default PageContextProvider;
