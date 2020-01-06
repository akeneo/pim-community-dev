import React, {FunctionComponent} from 'react';
import {createPortal} from 'react-dom';
import DataQualityInsightsTabContent from "./DataQualityInsightsTabContent";
import {usePageContext} from "../../../../infrastructure/hooks";

export const CONTAINER_ELEMENT_ID = 'data-quality-insights-product-tab-content';

const DataQualityInsightsTabContentPortal: FunctionComponent = () => {
  const portalContainer = document.getElementById(CONTAINER_ELEMENT_ID);
  const {dqiTabContentVisibility} = usePageContext();

  if (!portalContainer) {
    return null;
  }

  return createPortal(
    <>
      {dqiTabContentVisibility && (
        <DataQualityInsightsTabContent />
      )}
    </>,
    portalContainer
  );
};

export default DataQualityInsightsTabContentPortal;
