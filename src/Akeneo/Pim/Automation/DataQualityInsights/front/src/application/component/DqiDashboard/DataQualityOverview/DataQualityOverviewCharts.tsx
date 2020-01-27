import React, {useEffect, useState} from 'react';
import {useFetchDqiDashboardData} from "../../../../infrastructure/hooks";
import AxisChart from "./AxisChart";
import {DataQualityOverviewChartHeader} from "../index";

const __ = require('oro/translator');
const UserContext = require('pim/user-context');

type Ranks = {
  [rank: string]: number;
}

type AxisRates = {
  [date: string]: Ranks;
};

type Dataset = {
  [axisName: string]: AxisRates;
};

const transformData = (dataset: Dataset, axisName: string): any => {
  if (Object.keys(dataset).length === 0) {
    return {};
  }

  let ranks: {[rank: string]: any[]} = {
    'rank_5': [],
    'rank_4': [],
    'rank_3': [],
    'rank_2': [],
    'rank_1': [],
    'rank_6': [],
  };

  Object.entries(dataset[axisName]).map(([date, ranksByDay]) => {

    if (Object.keys(ranksByDay).length === 0) {
      ranks['rank_5'].push({x: date, y: 0});
      ranks['rank_4'].push({x: date, y: 0});
      ranks['rank_3'].push({x: date, y: 0});
      ranks['rank_2'].push({x: date, y: 0});
      ranks['rank_1'].push({x: date, y: 0});
      ranks['rank_6'].push({x: date, y: 100});
    } else {
      Object.entries(ranksByDay).map(([rank, distribution]) => {
        ranks[rank].push({x: date, y: distribution});
      });
      ranks['rank_6'].push({x: date, y: 0});
    }
  });

  return ranks;
};

interface DataQualityOverviewChartProps {
  catalogLocale: string;
  catalogChannel: string;
  timePeriod: string;
  familyCode: string | null;
  categoryCode: string | null;
}

const DataQualityOverviewCharts = ({catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode}: DataQualityOverviewChartProps) => {

  const [isLoading, setIsLoading] = useState(true);
  const [enrichmentChart, setEnrichmentChart] = useState();
  const [consistencyChart, setConsistencyChart] = useState();

  const dataset = useFetchDqiDashboardData(catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode);

  useEffect(() => {
    if (dataset === null) {
      return;
    }
    // @ts-ignore
    let enrichmentChart = getChart(transformData(dataset, 'enrichment'));
    // @ts-ignore
    let consistencyChart = getChart(transformData(dataset, 'consistency'));
    setEnrichmentChart(enrichmentChart);
    setConsistencyChart(consistencyChart);
    setIsLoading(false);
  }, [dataset]);

  useEffect(() => {
    setIsLoading(true);
  }, [catalogChannel, catalogLocale, timePeriod, familyCode, categoryCode]);

  const getChart = (dataset: any) => {
    return (
      <>
        {timePeriod === 'daily' && (<AxisChart dataset={dataset} padding={63} barRatio={1.29} dateFormatCallback={dailyCallback}/>)}
        {timePeriod === 'weekly' && (<AxisChart dataset={dataset} padding={117} barRatio={1.85} dateFormatCallback={weeklyCallback}/>)}
        {timePeriod === 'monthly' && (<AxisChart dataset={dataset} padding={73} barRatio={1.42} dateFormatCallback={monthlyCallback}/>)}
      </>
    );
  };

  if (dataset !== null && Object.entries(dataset).length === 0) {
    return (
      <>
        <div className="AknAssetPreview-imageContainer">
          <img src={"bundles/pimui/images/illustrations/Project.svg"} alt="illustrations/Project.svg"/>
        </div>
        <div className="AknInfoBlock">
          <p>{__(`akeneo_data_quality_insights.dqi_dashboard.no_data_title`)}</p>
          <p>{__(`akeneo_data_quality_insights.dqi_dashboard.no_data_subtitle`)}</p>
        </div>
      </>
    )
  }

  const weeklyCallback = (date: string) => {
    const uiLocale = UserContext.get('uiLocale');

    const endDate = new Date(date);
    const startDate = new Date(date);
    startDate.setDate(startDate.getDate() - 6);

    return new Intl.DateTimeFormat(uiLocale.replace('_', '-')).format(startDate) + ' - ' + new Intl.DateTimeFormat(uiLocale.replace('_', '-')).format(endDate);
  };

  const dailyCallback = (date: string) => {
    const uiLocale = UserContext.get('uiLocale');
    return new Intl.DateTimeFormat(
      uiLocale.replace('_', '-'),
      {weekday: "long", month: "long", day: "numeric"}
    ).format(new Date(date));
  };

  const monthlyCallback = (date: string) => {
    const uiLocale = UserContext.get('uiLocale');
    return new Intl.DateTimeFormat(
      uiLocale.replace('_', '-'),
      {month: "long", year: "numeric"}
    ).format(new Date(date));
  };

  return (
    <>
      <DataQualityOverviewChartHeader axisName={__(`akeneo_data_quality_insights.product_evaluation.axis.enrichment.title`)} displayLegend={true}/>
      <div className='AknDataQualityInsights-chart'>
        {isLoading && <div className="AknLoadingMask"/>}
        {enrichmentChart}
      </div>

      <DataQualityOverviewChartHeader axisName={__(`akeneo_data_quality_insights.product_evaluation.axis.consistency.title`)} displayLegend={false}/>
      <div className='AknDataQualityInsights-chart'>
        {isLoading && <div className="AknLoadingMask"/>}
        {consistencyChart}
      </div>
    </>
  )
};

export default DataQualityOverviewCharts;
