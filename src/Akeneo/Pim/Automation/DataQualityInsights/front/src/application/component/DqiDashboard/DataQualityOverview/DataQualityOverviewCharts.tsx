import React, {Fragment} from 'react';
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
    }
  });

  return ranks;
};

interface DataQualityOverviewChartProps {
  catalogLocale: string;
  catalogChannel: string;
  periodicity: string;
  familyCode: string | null;
}

const DataQualityOverviewCharts = ({catalogChannel, catalogLocale, periodicity, familyCode}: DataQualityOverviewChartProps) => {
  const dataset = useFetchDqiDashboardData(catalogChannel, catalogLocale, periodicity, familyCode);

  if (Object.entries(dataset).length === 0) {

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

  let i = 0;

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
      {
        Object.keys(dataset).map((axisName: string) => {
          const axisDataset = transformData(dataset, axisName);
          i++;
          return (
            <Fragment key={i}>
              <DataQualityOverviewChartHeader axisName={axisName} displayLegend={i === 1}/>
              <div className='AknDataQualityInsights-chart'>
                {periodicity === 'daily' && (<AxisChart dataset={axisDataset} padding={71} barRatio={1.49} dateFormatCallback={dailyCallback}/>)}
                {periodicity === 'weekly' && (<AxisChart dataset={axisDataset} padding={125} barRatio={1.99} dateFormatCallback={weeklyCallback}/>)}
                {periodicity === 'monthly' && (<AxisChart dataset={axisDataset} padding={80} barRatio={1.59} dateFormatCallback={monthlyCallback}/>)}
              </div>
            </Fragment>
          )
        })
      }
    </>
  )
};

export default DataQualityOverviewCharts;
