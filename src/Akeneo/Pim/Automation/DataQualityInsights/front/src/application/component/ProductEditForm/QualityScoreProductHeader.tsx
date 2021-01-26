import React from 'react';
import {QualityScoreBar} from '../QualityScoreBar';
import {useCatalogContext, useFetchProductQualityScore} from '../../../infrastructure/hooks';

const QualityScoreProductHeader = () => {
  const {channel, locale} = useCatalogContext();
  const score = useFetchProductQualityScore(channel, locale);

  return <QualityScoreBar currentScore={score ? score : null} />;
};

export {QualityScoreProductHeader};
