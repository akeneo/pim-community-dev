import React from 'react';
import {QualityScoreBar} from '../QualityScoreBar';
import {QualityScoreLoader} from '../QualityScoreLoader';
import {DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB} from '../../listener';
import {useCatalogContext, useFetchQualityScore} from '../../../infrastructure/hooks';
import {Badge} from 'akeneo-design-system';
import styled from 'styled-components';

const Border = styled.div`
  border-right: 1px ${({theme}) => theme.color.grey80} solid;
`;

const Wrapper = styled.div`
  margin-right: 20px;
`;

const QualityScoreProductHeader = () => {
  const {channel, locale} = useCatalogContext();
  const {score, productType, isLoading} = useFetchQualityScore(channel, locale);
  const redirectToDqiTab = () => window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB));

  let qualityScoreComponent;
  if (isLoading) {
    qualityScoreComponent = <QualityScoreLoader />;
  } else if (score === 'N/A' || score === null) {
    qualityScoreComponent = <Badge level="tertiary">in progress</Badge>;
  } else {
    qualityScoreComponent = (
      <QualityScoreBar currentScore={score} stacked={productType === 'product_model'} onClick={redirectToDqiTab} />
    );
  }

  return (
    <Wrapper>
      {qualityScoreComponent}
      <Border />
    </Wrapper>
  );

  /*
  return isLoading ? (
    <QualityScoreLoader />
  ) : (
    <QualityScoreBar currentScore={score} stacked={productType === 'product_model'} onClick={redirectToDqiTab} />
  );*/
};

export {QualityScoreProductHeader};
