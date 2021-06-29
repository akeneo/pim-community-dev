import React, {FunctionComponent} from 'react';
import {createPortal} from 'react-dom';
import {QualityScoreProductHeader} from './QualityScoreProductHeader';

export const CONTAINER_ELEMENT_ID = 'data-quality-insights-product-quality-score';

const QualityScorePortal: FunctionComponent = () => {
  const portalContainer = document.getElementById(CONTAINER_ELEMENT_ID);

  return portalContainer && createPortal(<QualityScoreProductHeader />, portalContainer);
};

export default QualityScorePortal;
