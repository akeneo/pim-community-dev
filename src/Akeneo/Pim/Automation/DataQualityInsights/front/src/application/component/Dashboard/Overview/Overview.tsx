import React, {ReactElement, useEffect, useState, ReactElement} from 'react';
import {isEmpty} from "lodash";
import {useFetchDqiDashboardData} from "../../../../infrastructure/hooks";
import {Dataset, formatBackendRanksToVictoryFormat} from "../../../helper/Dashboard/FormatBackendRanksToVictoryFormat";
import Filters from "./Filters";
import Header from "./Charts/Header";
import EmptyChartPlaceholder from "./Charts/EmptyChartPlaceholder";
import TimePeriodAxisChart from "./Charts/TimePeriodAxisChart";

const __ = require('oro/translator');

interface DataQualityOverviewChartProps {
  catalogLocale: string;
  catalogChannel: string;
  timePeriod: string;
  familyCode: string | null;
  categoryCode: string | null;
}

const showOverviewPlaceholder = (dataset: Dataset|null) => {
  return (dataset !== null) && (
    isEmpty(dataset) ||
    (
      // @ts-ignore
      (isEmpty(dataset['enrichment']) || Object.entries(dataset['enrichment']).every(([date, ranksData]) => isEmpty(ranksData))) &&
      // @ts-ignore
      (isEmpty(dataset['consistency']) || Object.entries(dataset['consistency']).every(([date, ranksData]) => isEmpty(ranksData)))
    )
  );
};

const Overview = ({catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode}: DataQualityOverviewChartProps) => {

  const [isLoading, setIsLoading] = useState(true);
  const [enrichmentChart, setEnrichmentChart] = useState<ReactElement>();
  const [consistencyChart, setConsistencyChart] = useState<ReactElement>();

  const dataset = useFetchDqiDashboardData(catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode);

  useEffect(() => {
    if (dataset === null) {
      return;
    }

    const enrichmentChartDataset = formatBackendRanksToVictoryFormat(dataset, 'enrichment');
    setEnrichmentChart(<TimePeriodAxisChart dataset={enrichmentChartDataset} timePeriod={timePeriod}/>);

    const consistencyChartDataset = formatBackendRanksToVictoryFormat(dataset, 'consistency');
    setConsistencyChart(<TimePeriodAxisChart dataset={consistencyChartDataset} timePeriod={timePeriod}/>);

    setIsLoading(false);
  }, [dataset]);

  useEffect(() => {
    setIsLoading(true);
  }, [catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode]);

  return (
    <>
      <Filters timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode}/>
      {showOverviewPlaceholder(dataset) ? (
        <EmptyChartPlaceholder />
      ) : (
        <>
          <Header axisName={__(`akeneo_data_quality_insights.product_evaluation.axis.enrichment.title`)} displayLegend={true}/>
          <div className='AknDataQualityInsights-chart'>
            {isLoading && <div className="AknLoadingMask"/>}
            {enrichmentChart}
          </div>

          <Header axisName={__(`akeneo_data_quality_insights.product_evaluation.axis.consistency.title`)} displayLegend={false}/>
          <div className='AknDataQualityInsights-chart'>
            {isLoading && <div className="AknLoadingMask"/>}
            {consistencyChart}
          </div>
        </>
      )}
    </>
  )
};

export default Overview;
