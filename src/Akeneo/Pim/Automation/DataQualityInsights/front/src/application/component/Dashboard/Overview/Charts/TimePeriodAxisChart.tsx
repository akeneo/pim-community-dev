import {isEmpty} from "lodash";
import React, {FunctionComponent} from "react";
import EmptyChartPlaceholder from "./EmptyChartPlaceholder";
import AxisChart from "./AxisChart";
import {dailyCallback, monthlyCallback, weeklyCallback} from "../../../../helper/Dashboard/FormatDateWithUserLocale";

export type ChartDataset = {
  [rank: string]: ChartData[];
};

export type ChartData = {
  x: string;
  y: number;
};

const isEmptyChartDataset = (dataset: ChartDataset): boolean => {
  if (isEmpty(dataset) || isEmpty(dataset['rank_6'])) {
    return true;
  }

  return dataset['rank_6'].every((data) => {
    return (data.y === 100);
  });
};


export interface TimePeriodAxisChartProps {
  dataset: ChartDataset;
  timePeriod: string;
}

const TimePeriodAxisChart: FunctionComponent<TimePeriodAxisChartProps> = ({dataset, timePeriod}) => {
  return (
    <>
      {isEmptyChartDataset(dataset) ? (
        <EmptyChartPlaceholder />
      ) : (
        <>
          {timePeriod === 'daily' && (<AxisChart dataset={dataset} padding={63} barRatio={1.29} dateFormatCallback={dailyCallback}/>)}
          {timePeriod === 'weekly' && (<AxisChart dataset={dataset} padding={117} barRatio={1.85} dateFormatCallback={weeklyCallback}/>)}
          {timePeriod === 'monthly' && (<AxisChart dataset={dataset} padding={73} barRatio={1.42} dateFormatCallback={monthlyCallback}/>)}
        </>
      )}
    </>
  );
};

export default TimePeriodAxisChart;
