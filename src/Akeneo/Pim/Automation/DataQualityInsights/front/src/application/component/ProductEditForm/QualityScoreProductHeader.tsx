import React from 'react';
import {QualityScoreBar} from '../QualityScoreBar';
import {useCatalogContext, useFetchProductQualityScore} from '../../../infrastructure/hooks';

const QualityScoreProductHeader = () => {
  const {channel, locale} = useCatalogContext();
  const {score, isLoading} = useFetchProductQualityScore(channel, locale);

  return isLoading
    ? <div>Loading...</div>
    : <QualityScoreBar currentScore={score ? score : null}/>;
};

export {QualityScoreProductHeader};
