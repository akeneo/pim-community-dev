import React, {Fragment} from 'react';
import {useFetchDqiDashboardData} from "../../../../infrastructure/hooks";
import AxisChart from "./AxisChart";
import {DataQualityOverviewChartHeader} from "../index";

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
    'rank_1': [],
    'rank_2': [],
    'rank_3': [],
    'rank_4': [],
    'rank_5': [],
    'rank_6': []
  };

  Object.entries(dataset[axisName]).map(([date, ranksByDay]) => {
    if (Object.keys(ranksByDay).length === 0) {
      ranks['rank_1'].push({x: date, y: 0});
      ranks['rank_2'].push({x: date, y: 0});
      ranks['rank_3'].push({x: date, y: 0});
      ranks['rank_4'].push({x: date, y: 0});
      ranks['rank_5'].push({x: date, y: 0});
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
}

const DataQualityOverviewCharts = ({catalogChannel, catalogLocale}: DataQualityOverviewChartProps) => {
  const myDataset = useFetchDqiDashboardData(catalogChannel, catalogLocale);

  if (Object.entries(myDataset).length === 0) {
    return (<></>);
  }


  // if (Object.entries(dataset).length === 0) {
  //   return (<></>);
  // }

  let i = 0;

  return (
    <>
      {
        Object.keys(myDataset).map((axisName: string) => {
          const dataset = transformData(myDataset, axisName);
          i++;
          return (
            <Fragment key={i}>
              <DataQualityOverviewChartHeader axisName={axisName} displayLegend={i === 1}/>
              <div className='AknDataQualityInsights-chart'>
                <AxisChart dataset={dataset}/>
                {/*{isVisible && (<CustomTooltip/>)}*/}
              </div>
            </Fragment>
          )
        })
      }
    </>
  )
};

export default DataQualityOverviewCharts;
