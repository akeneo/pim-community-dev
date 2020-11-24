import React, {FC, ReactElement, useEffect, useState} from 'react';
import {isEmpty} from 'lodash';
import {useFetchDqiDashboardData} from '../../../../infrastructure/hooks';
import {formatBackendRanksToVictoryFormat} from '../../../helper/Dashboard';
import {Header} from './Header';
import {EmptyChartPlaceholder, Legend, ScoreDistributionChartByTimePeriod} from './Chart';
import {ScoreDistributionByDate, TimePeriod} from '../../../../domain';

type Props = {
  catalogLocale: string;
  catalogChannel: string;
  timePeriod: TimePeriod;
  familyCode: string | null;
  categoryCode: string | null;
};

const showPlaceholder = (dataset: ScoreDistributionByDate | null) => {
  return (
    dataset !== null && (isEmpty(dataset) || Object.entries(dataset).every(([_, ranksData]) => isEmpty(ranksData)))
  );
};

const ScoreDistributionSection: FC<Props> = ({catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode}) => {
  const [isLoading, setIsLoading] = useState(true);
  const [chart, setChart] = useState<ReactElement | null>(null);
  const dataset = useFetchDqiDashboardData(catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode);

  useEffect(() => {
    if (dataset === null) {
      return;
    }

    try {
      const formattedDataset = formatBackendRanksToVictoryFormat(dataset);
      setChart(<ScoreDistributionChartByTimePeriod dataset={formattedDataset} timePeriod={timePeriod} />);
    } catch (error) {
      console.error(error);
    }

    setIsLoading(false);
  }, [dataset]);

  useEffect(() => {
    setIsLoading(true);
  }, [catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode]);

  const isPlaceholderVisible: boolean = showPlaceholder(dataset) || chart === null;

  return (
    <>
      <Header
        timePeriod={timePeriod}
        familyCode={familyCode}
        categoryCode={categoryCode}
        showFilters={!isPlaceholderVisible}
      />
      {isPlaceholderVisible ? (
        <EmptyChartPlaceholder />
      ) : (
        <>
          <Legend />
          <div className="AknDataQualityInsights-chart">
            {isLoading && <div className="AknLoadingMask" />}
            {chart}
          </div>
        </>
      )}
    </>
  );
};

export {ScoreDistributionSection};
