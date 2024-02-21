import React, {useEffect} from 'react';
import {useSelector} from 'react-redux';
import styled from 'styled-components';

import {getColor, getFontFamily, getFontSize} from 'akeneo-design-system';

import {useTranslate} from '@akeneo-pim-community/shared';

import {ProductEditFormState} from '../../../infrastructure/store';
import {useCatalogContext, useFetchQualityScore} from '../../../infrastructure/hooks';
import {QualityScoreBarPEF} from './QualityScoreBarPEF';
import {QualityScoreLoader} from '../QualityScoreLoader';
import {ProductType} from '../../../domain/Product.interface';
import {QualityScorePending} from '../QualityScorePending';

type StateExtract = {
  id: string | null;
  type: ProductType;
  isProductEvaluating: boolean;
};

const selector = ({
  product: {
    meta: {id, model_type},
  },
  pageContext: {isProductEvaluating},
}: ProductEditFormState): StateExtract => ({
  id,
  type: model_type as ProductType,
  isProductEvaluating,
});

const QualityScoreProductHeader = () => {
  const translate = useTranslate();
  const {channel, locale} = useCatalogContext();
  const {id, type, isProductEvaluating} = useSelector(selector);
  const {outcome: scoresFetchingOutcome, fetcher: fetchScores} = useFetchQualityScore(type, id);

  useEffect(() => {
    // initial fetch, or when entity identifier change
    fetchScores();
  }, [id, type]);

  useEffect(() => {
    // we should try to reload only when isProductEvaluating is going from false to true
    // this means an evaluation is in progress after a save we've made
    if (isProductEvaluating) {
      fetchScores();
    }
  }, [isProductEvaluating]);

  let qualityScoreComponent: React.ReactNode;
  switch (scoresFetchingOutcome.status) {
    case 'init': // no break
    case 'loading': {
      // if the user just saved the product and score loading is the direct consequence of that
      // then we display the loader
      // in the other case (just editing the product, user did not save yet)
      // then we consider we are in a possible long evaluation process (mass product import …)
      // and therefore we display the "in progress" message to express a possibly long time before we get the score
      qualityScoreComponent = isProductEvaluating ? <QualityScoreLoader /> : <QualityScorePending />;
      break;
    }
    case 'loaded': {
      qualityScoreComponent = (
        <QualityScoreBarPEF score={scoresFetchingOutcome.scores[channel][locale]} stacked={type == 'product_model'} />
      );
      break;
    }
    default: {
      // 'failed' or 'retries exhausted' : tell user we dont have the score, in a non-agresive way
      qualityScoreComponent = <QualityScorePending />;
      break;
    }
  }

  return (
    <Wrapper>
      <Label>{translate('akeneo_data_quality_insights.quality_score.title')}</Label>
      {qualityScoreComponent}
    </Wrapper>
  );
};

const Wrapper = styled.div`
  display: flex;
  flex-flow: row nowrap;
  align-items: center;
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
