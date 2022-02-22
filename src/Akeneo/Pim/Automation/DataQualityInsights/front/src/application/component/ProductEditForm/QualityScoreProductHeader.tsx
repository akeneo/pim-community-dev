import React from 'react';
import {QualityScoreBar} from '../QualityScoreBar';
import {useCatalogContext, useFetchProductQualityScore} from '../../../infrastructure/hooks';
import {QualityScoreLoader} from '../QualityScoreLoader';
import {DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB} from '../../listener';

const QualityScoreProductHeader = () => {
  const {channel, locale} = useCatalogContext();
  const {score, isLoading} = useFetchProductQualityScore(channel, locale);
  const redirectToDqiTab = () => window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB));

  return isLoading ? <QualityScoreLoader /> : <QualityScoreBar currentScore={score} onClick={redirectToDqiTab} />;
};

export {QualityScoreProductHeader};
