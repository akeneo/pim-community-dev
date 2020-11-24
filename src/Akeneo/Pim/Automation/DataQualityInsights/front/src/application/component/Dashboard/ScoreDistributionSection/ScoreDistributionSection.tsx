import React, {FC, ReactElement, useEffect, useState} from 'react';
import {isEmpty} from 'lodash';
import {useFetchDqiDashboardData} from '../../../../infrastructure/hooks';
import {formatBackendRanksToVictoryFormat} from '../../../helper/Dashboard';
import {Header} from './Header';
import {EmptyChartPlaceholder, Legend, TimePeriodAxisChart} from './Chart';
import {ScoreDistributionByDate} from '../../../../domain';

type Props = {
  catalogLocale: string;
  catalogChannel: string;
  timePeriod: string;
  familyCode: string | null;
  categoryCode: string | null;
};

const showOverviewPlaceholder = (dataset: ScoreDistributionByDate | null) => {
  return (
    dataset !== null && (isEmpty(dataset) || Object.entries(dataset).every(([_, ranksData]) => isEmpty(ranksData)))
  );
};

const ScoreDistributionSection: FC<Props> = ({catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode}) => {
  const [isLoading, setIsLoading] = useState(true);
  const [chart, setChart] = useState<ReactElement>();
  const dataset = useFetchDqiDashboardData(catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode);

  useEffect(() => {
    if (dataset === null) {
      return;
    }

    const formattedDataset = formatBackendRanksToVictoryFormat(dataset);
    setChart(<TimePeriodAxisChart dataset={formattedDataset} timePeriod={timePeriod} />);

    setIsLoading(false);
  }, [dataset]);

  useEffect(() => {
    setIsLoading(true);
  }, [catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode]);

  return (
    <>
      <Header timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode} />
      {showOverviewPlaceholder(dataset) ? (
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
