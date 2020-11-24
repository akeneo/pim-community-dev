import React, {ReactElement, useEffect, useState} from 'react';
import {isEmpty} from 'lodash';
import {useFetchDqiDashboardData} from '../../../../infrastructure/hooks';
import {formatBackendRanksToVictoryFormat} from '../../../helper/Dashboard';
import Filters from './Filters';
import Header from './Charts/Header';
import EmptyChartPlaceholder from './Charts/EmptyChartPlaceholder';
import TimePeriodAxisChart from './Charts/TimePeriodAxisChart';
import {ScoreDistributionByDate} from '../../../../domain';

const __ = require('oro/translator');

interface DataQualityOverviewChartProps {
  catalogLocale: string;
  catalogChannel: string;
  timePeriod: string;
  familyCode: string | null;
  categoryCode: string | null;
}

const showOverviewPlaceholder = (dataset: ScoreDistributionByDate | null) => {
  return (
    dataset !== null && (isEmpty(dataset) || Object.entries(dataset).every(([_, ranksData]) => isEmpty(ranksData)))
  );
};

const Overview = ({
  catalogChannel,
  catalogLocale,
  timePeriod,
  familyCode,
  categoryCode,
}: DataQualityOverviewChartProps) => {
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
      <Filters timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode} />
      {showOverviewPlaceholder(dataset) ? (
        <EmptyChartPlaceholder />
      ) : (
        <>
          <Header
            axisName={__(`akeneo_data_quality_insights.product_evaluation.axis.enrichment.title`)}
            displayLegend={true}
          />
          <div className="AknDataQualityInsights-chart">
            {isLoading && <div className="AknLoadingMask" />}
            {chart}
          </div>
        </>
      )}
    </>
  );
};

export default Overview;
