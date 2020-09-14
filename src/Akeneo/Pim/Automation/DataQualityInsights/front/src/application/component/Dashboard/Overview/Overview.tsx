import React, {ReactElement, useEffect, useState} from 'react';
import {isEmpty} from "lodash";
import {useFetchDqiDashboardData} from "../../../../infrastructure/hooks";
import {Dataset, formatBackendRanksToVictoryFormat} from "../../../helper/Dashboard/FormatBackendRanksToVictoryFormat";
import Filters from "./Filters";
import Header from "./Charts/Header";
import EmptyChartPlaceholder from "./Charts/EmptyChartPlaceholder";
import TimePeriodAxisChart from "./Charts/TimePeriodAxisChart";
import {AxesContextState, useAxesContext} from "../../../context/AxesContext";
const __ = require('oro/translator');

interface DataQualityOverviewChartProps {
  catalogLocale: string;
  catalogChannel: string;
  timePeriod: string;
  familyCode: string | null;
  categoryCode: string | null;
}

const showOverviewPlaceholder = (dataset: Dataset|null, axesContext: AxesContextState) => {
  return (dataset !== null) && (
    isEmpty(dataset) ||
    (
      // @ts-ignore
      (isEmpty(dataset['enrichment']) || Object.entries(dataset['enrichment']).every(([date, ranksData]) => isEmpty(ranksData))) &&
      // @ts-ignore
      (!axesContext.axes.includes('consistency') || isEmpty(dataset['consistency']) || Object.entries(dataset['consistency']).every(([date, ranksData]) => isEmpty(ranksData)))
    )
  );
};

const Overview = ({catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode}: DataQualityOverviewChartProps) => {

  const [isLoading, setIsLoading] = useState(true);
  const [enrichmentChart, setEnrichmentChart] = useState<ReactElement>();
  const [consistencyChart, setConsistencyChart] = useState<ReactElement>();
  const axesContext = useAxesContext();

  const dataset = useFetchDqiDashboardData(catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode);

  useEffect(() => {
    if (dataset === null) {
      return;
    }

    const enrichmentChartDataset = formatBackendRanksToVictoryFormat(dataset, 'enrichment');
    setEnrichmentChart(<TimePeriodAxisChart dataset={enrichmentChartDataset} timePeriod={timePeriod}/>);

    if (axesContext.axes.includes('consistency')) {
      const consistencyChartDataset = formatBackendRanksToVictoryFormat(dataset, 'consistency');
      setConsistencyChart(<TimePeriodAxisChart dataset={consistencyChartDataset} timePeriod={timePeriod}/>);
    }

    setIsLoading(false);
  }, [dataset]);

  useEffect(() => {
    setIsLoading(true);
  }, [catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode]);

  return (
    <>
      <Filters timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode}/>
      {showOverviewPlaceholder(dataset, axesContext) ? (
        <EmptyChartPlaceholder />
      ) : (
        <>
          <Header axisName={__(`akeneo_data_quality_insights.product_evaluation.axis.enrichment.title`)} displayLegend={true}/>
          <div className='AknDataQualityInsights-chart'>
            {isLoading && <div className="AknLoadingMask"/>}
            {enrichmentChart}
          </div>

          {axesContext.axes.includes('consistency') &&
            <>
              <Header axisName={__(`akeneo_data_quality_insights.product_evaluation.axis.consistency.title`)} displayLegend={false}/>
              <div className='AknDataQualityInsights-chart'>
                {isLoading && <div className="AknLoadingMask"/>}
                {consistencyChart}
              </div>
            </>
          }
        </>
      )}
    </>
  )
};

export default Overview;
