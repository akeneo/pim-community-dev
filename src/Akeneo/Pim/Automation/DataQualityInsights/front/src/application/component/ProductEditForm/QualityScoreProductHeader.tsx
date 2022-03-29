import React from 'react';
import {QualityScoreBar} from '../QualityScoreBar';
import {QualityScoreLoader} from '../QualityScoreLoader';
import {DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB} from '../../listener';
import {useCatalogContext, useFetchQualityScore} from '../../../infrastructure/hooks';
import {getColor, getFontFamily, getFontSize} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {QualityScorePending} from '../QualityScorePending';

const QualityScoreProductHeader = () => {
  const translate = useTranslate();
  const {channel, locale} = useCatalogContext();
  const {score, productType, isLoading} = useFetchQualityScore(channel, locale);
  const redirectToDqiTab = () => window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB));
  const isPending = score === 'N/A' || score === null;

  let alignItems: 'baseline' | 'center' = 'center';

  let qualityScoreComponent: JSX.Element;
  if (isLoading) {
    qualityScoreComponent = <QualityScoreLoader />;
  } else if (isPending) {
    qualityScoreComponent = <QualityScorePending />;
  } else {
    if (score !== 'A') {
      alignItems = 'baseline';
    }
    qualityScoreComponent = (
      <QualityScoreBar score={score} stacked={productType === 'product_model'} onClick={redirectToDqiTab} />
    );
  }

  return (
    <Wrapper alignItems={alignItems}>
      <Label>{translate('akeneo_data_quality_insights.quality_score.title')}</Label>
      {qualityScoreComponent}
    </Wrapper>
  );
};

const Wrapper = styled.div<{alignItems: 'baseline' | 'center'}>`
  display: flex;
  flex-flow: row nowrap;
  align-items: ${({alignItems}) => alignItems};
  padding-right: 20px;
  margin-right: 20px;
  height: 20px;
  overflow: visible;
  border-right: 1px ${({theme}) => theme.color.grey80} solid;
`;

const Label = styled.div`
  padding-right: 0.4em;
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('default')};
  font-family: ${getFontFamily('default')};
  font-weight: normal;
  white-space: nowrap;
  height: 100%;
`;

export {QualityScoreProductHeader};
