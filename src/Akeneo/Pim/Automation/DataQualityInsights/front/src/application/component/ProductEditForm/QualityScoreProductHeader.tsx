import React from 'react';
import {QualityScoreBar} from '../QualityScoreBar';
import {QualityScoreLoader} from '../QualityScoreLoader';
import {DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB} from '../../listener';
import {useCatalogContext, useFetchQualityScore} from '../../../infrastructure/hooks';

const QualityScoreProductHeader = () => {
  const {channel, locale} = useCatalogContext();
  const {score, productType, isLoading} = useFetchQualityScore(channel, locale);
  const redirectToDqiTab = () => window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB));

  return isLoading ? (
    <QualityScoreLoader />
  ) : (
    <QualityScoreBar currentScore={score} stacked={productType === 'product_model'} onClick={redirectToDqiTab} />
  );
};

export {QualityScoreProductHeader};
