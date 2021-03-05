import React, {FC, ReactElement, useEffect, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {QualityScoreEvolutionChart} from './QualityScoreEvolutionChart';
import {RawScoreEvolutionData, useFetchQualityScoreEvolution} from '../../../../infrastructure/hooks';
import {EmptyChartPlaceholder} from '../EmptyChartPlaceholder';
import styled from 'styled-components';
import {Scoring} from 'akeneo-design-system';

type Props = {
  locale: string;
  channel: string;
  familyCode: string | null;
  categoryCode: string | null;
};

const showPlaceholder = (dataset: RawScoreEvolutionData | null) => {
  return dataset !== null && Object.values(dataset.data).filter((score: string | null) => score !== null).length === 0;
};

const QualityScoreEvolutionSection: FC<Props> = ({categoryCode, familyCode, channel, locale}) => {
  const translate = useTranslate();
  const dataset: RawScoreEvolutionData | null = useFetchQualityScoreEvolution(
    channel,
    locale,
    familyCode,
    categoryCode
  );
  const [chart, setChart] = useState<ReactElement | null>(null);

  useEffect(() => {
    if (dataset === null) {
      return;
    }

    setChart(
      <>
        <AverageScoreLabel>
          {dataset.average_rank === null ? (
            <>{translate('akeneo_data_quality_insights.dqi_dashboard.quality_score_evolution.no_catalog_score')}</>
          ) : (
            <>
              <Scoring score={dataset.average_rank} />
              <span style={{marginLeft: '8px'}}>
                {translate('akeneo_data_quality_insights.dqi_dashboard.quality_score_evolution.current_score')}
              </span>
            </>
          )}
        </AverageScoreLabel>
        <QualityScoreEvolutionChart rawDataset={dataset.data} />
      </>
    );
  }, [dataset]);

  return (
    <Container>
      <div className="AknSubsection-title">
        <span>{translate('akeneo_data_quality_insights.dqi_dashboard.quality_score_evolution.title')}</span>
      </div>

      <ChartContainer>
        {dataset === null && <div className="AknLoadingMask" />}
        {showPlaceholder(dataset) ? <EmptyChartPlaceholder /> : chart}
      </ChartContainer>
    </Container>
  );
};

const ChartContainer = styled.div`
  position: relative;
  min-height: 250px;
`;

const Container = styled.div`
  padding-right: 20px;
  flex: 1 0 50%;
`;

const AverageScoreLabel = styled.div`
  color: ${({theme}) => theme.color.grey140};
  margin-top: 20px;
  margin-bottom: 15px;
`;

export {QualityScoreEvolutionSection};
