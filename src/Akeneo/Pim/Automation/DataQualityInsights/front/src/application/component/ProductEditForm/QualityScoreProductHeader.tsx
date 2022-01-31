import React from 'react';
import {QualityScoreBar} from '../QualityScoreBar';
import {useCatalogContext, useFetchProductQualityScore} from '../../../infrastructure/hooks';
import {QualityScoreLoader} from '../QualityScoreLoader';

const QualityScoreProductHeader = () => {
  const {channel, locale} = useCatalogContext();
  const {score, isLoading} = useFetchProductQualityScore(channel, locale);
  return isLoading ? <QualityScoreLoader /> : <QualityScoreBar currentScore={score ? score : null} />;
};

export {QualityScoreProductHeader};
