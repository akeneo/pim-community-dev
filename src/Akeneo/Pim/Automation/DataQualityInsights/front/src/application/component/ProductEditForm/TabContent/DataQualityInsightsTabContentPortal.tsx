import React, {FunctionComponent} from 'react';
import {createPortal} from 'react-dom';
import DataQualityInsightsTabContent from "./DataQualityInsightsTabContent";
import {usePageContext} from "../../../../infrastructure/hooks";
import {DATA_QUALITY_INSIGHTS_TAB_NAME} from "../../../constant";

export const CONTAINER_ELEMENT_ID = 'data-quality-insights-product-tab-content';

const isVisible = (currentTab: string) => {
  return currentTab === DATA_QUALITY_INSIGHTS_TAB_NAME;
};

const DataQualityInsightsTabContentPortal: FunctionComponent = () => {
  const portalContainer = document.getElementById(CONTAINER_ELEMENT_ID);
  const {currentTab} = usePageContext();

  return portalContainer && createPortal(
    <>
      {isVisible(currentTab) && (
        <DataQualityInsightsTabContent />
      )}
    </>,
    portalContainer
  );
};

export default DataQualityInsightsTabContentPortal;
