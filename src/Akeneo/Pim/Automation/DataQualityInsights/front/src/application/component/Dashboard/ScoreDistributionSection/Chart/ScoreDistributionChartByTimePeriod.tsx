import {isEmpty} from 'lodash';
import React, {FC, useMemo} from 'react';
import {ScoreDistributionChart} from './ScoreDistributionChart';
import {dailyCallback, monthlyCallback, weeklyCallback} from '../../../../helper/Dashboard';
import {ScoreDistributionChartDataset, TimePeriod} from '../../../../../domain';
import {EmptyChartPlaceholder} from '../../EmptyChartPlaceholder';

const isEmptyChartDataset = (dataset: ScoreDistributionChartDataset): boolean => {
  if (isEmpty(dataset) || isEmpty(dataset['rank_6'])) {
    return true;
  }

  return dataset['rank_6'].every(data => {
    return data.y === 100;
  });
};

export interface Props {
  dataset: ScoreDistributionChartDataset;
  timePeriod: TimePeriod;
}

const ScoreDistributionChartByTimePeriod: FC<Props> = ({dataset, timePeriod}) => {
  const callback = useMemo(() => {
    if (timePeriod === 'daily') {
      return dailyCallback;
    }
    if (timePeriod === 'monthly') {
      return monthlyCallback;
    }

    return weeklyCallback;
  }, [timePeriod]);

  const periods: number = useMemo(() => {
    if (timePeriod === 'daily') {
      return 7;
    }
    if (timePeriod === 'monthly') {
      return 6;
    }

    return 4;
  }, [timePeriod]);

  const domainPadding: number = useMemo(() => {
    if (timePeriod === 'weekly') {
      return 80;
    }

    return 30;
  }, [timePeriod]);

  return (
    <>
      {isEmptyChartDataset(dataset) ? (
        <EmptyChartPlaceholder />
      ) : (
        <ScoreDistributionChart
          dataset={dataset}
          periods={periods}
          dateFormatCallback={callback}
          domainPadding={domainPadding}
        />
      )}
    </>
  );
};

export {ScoreDistributionChartByTimePeriod};
