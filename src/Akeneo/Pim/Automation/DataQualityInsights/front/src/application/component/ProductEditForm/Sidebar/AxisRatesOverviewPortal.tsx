import React, {FunctionComponent} from 'react';
import {createPortal} from 'react-dom';
import AxisRatesOverview from "./AxisRatesOverview";

export const CONTAINER_ELEMENT_ID = 'data-quality-insights-product-overview';

const AxisRatesOverviewPortal: FunctionComponent = () => {
  const portalContainer = document.getElementById(CONTAINER_ELEMENT_ID);

  if (!portalContainer) {
    return null;
  }

  return createPortal(
    <AxisRatesOverview />,
    portalContainer
  );
};

export default AxisRatesOverviewPortal;
