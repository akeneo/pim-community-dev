import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from "react-redux";

import {changeDataQualityInsightsTabContentVisibility} from "../reducer";

interface TabContentVisibilityEvent {
  show: boolean;
}

interface PageContextProviderProps {
  children: any;
}

export const DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY_CHANGED = 'data-quality:dqi-tab-content:visibility:changed';

const PageContextProvider: FunctionComponent<PageContextProviderProps> = ({children}) => {
  const dispatchAction = useDispatch();

  useEffect(() => {
    window.addEventListener(DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY_CHANGED, ((event: CustomEvent<TabContentVisibilityEvent>) => {
      dispatchAction(changeDataQualityInsightsTabContentVisibility(event.detail.show));
    }) as EventListener);
  }, []);

  return (
    <>{children}</>
  )
};

export default PageContextProvider;
