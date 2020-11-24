import {isEmpty} from 'lodash';
import React, {FC} from 'react';
import {EmptyChartPlaceholder} from './EmptyChartPlaceholder';
import {AxisChart} from './AxisChart';
import {dailyCallback, monthlyCallback, weeklyCallback} from '../../../../helper/Dashboard';
import {ScoreDistributionChartDataset} from '../../../../../domain';

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
  timePeriod: string;
}

const TimePeriodAxisChart: FC<Props> = ({dataset, timePeriod}) => {
  return (
    <>
      {isEmptyChartDataset(dataset) ? (
        <EmptyChartPlaceholder />
      ) : (
        <>
          {timePeriod === 'daily' && <AxisChart dataset={dataset} dateFormatCallback={dailyCallback} />}
          {timePeriod === 'weekly' && <AxisChart dataset={dataset} dateFormatCallback={weeklyCallback} />}
          {timePeriod === 'monthly' && <AxisChart dataset={dataset} dateFormatCallback={monthlyCallback} />}
        </>
      )}
    </>
  );
};

export {TimePeriodAxisChart};
