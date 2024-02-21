import React, {FC, useEffect, useRef, useState} from 'react';
import {VictoryAxis, VictoryBar, VictoryChart, VictoryStack, VictoryTooltip} from 'victory';
import {useGetDashboardChartScalingSizeRatio} from '../../../../../infrastructure/hooks';
import {Tooltip} from './Tooltip';
import {useTheme} from 'akeneo-design-system';
import {ScoreDistributionChartDataset} from '../../../../../domain';

const INITIAL_CHART_WIDTH = 481;
const INITIAL_CHART_HEIGHT = 250;

type Props = {
  dataset: ScoreDistributionChartDataset;
  dateFormatCallback: {(date: string, index: number): string};
  periods: number;
  domainPadding: number;
};

const ScoreDistributionChart: FC<Props> = ({dataset, dateFormatCallback, periods, domainPadding}) => {
  const theme = useTheme();
  const chartContainerRef = useRef<HTMLDivElement | null>(null);
  const {upScalingRatio, downScalingRatio} = useGetDashboardChartScalingSizeRatio(
    chartContainerRef,
    INITIAL_CHART_WIDTH
  );
  const [colorScale, setColorScale] = useState<string[]>([]);

  useEffect(() => {
    if (!theme) {
      return;
    }

    setColorScale([
      theme.color.red100,
      theme.color.red60,
      theme.color.yellow60,
      theme.color.green100,
      theme.color.green60,
      theme.color.grey80,
    ]);
  }, [theme]);

  let dates: any[] = [];
  if (Object.entries(dataset).length > 0) {
    dates = Object.values(dataset['rank_1']).map((rate: any) => rate.x);
  }

  return (
    <div ref={chartContainerRef}>
      <VictoryChart
        height={INITIAL_CHART_HEIGHT}
        width={INITIAL_CHART_WIDTH}
        padding={{
          top: 0,
          bottom: Math.ceil(40 * downScalingRatio),
          left: Math.ceil(34 * downScalingRatio),
          right: 5,
        }}
        domain={{x: [1, periods], y: [0, 100]}}
        domainPadding={{x: domainPadding, y: [0, 12.5]}}
      >
        <VictoryAxis
          tickValues={dates}
          tickFormat={dateFormatCallback}
          style={{
            axis: {strokeWidth: 0},
            tickLabels: {
              fontSize: Math.ceil(parseInt(theme.fontSize.small) * downScalingRatio),
              fill: theme?.color.grey120,
              padding: Math.ceil(21 * downScalingRatio),
              fontFamily: 'Lato',
              textTransform: 'capitalize',
            },
          }}
        />
        <VictoryAxis
          dependentAxis
          orientation="left"
          standalone={false}
          tickValues={[0, 25, 50, 75, 100]}
          tickFormat={(value: number) => (value > 0 ? `${value}%` : '')}
          style={{
            grid: {
              stroke: theme?.color.grey60,
              strokeWidth: 1,
            },
            tickLabels: {
              fontSize: Math.ceil(parseInt(theme.fontSize.small) * downScalingRatio),
              fill: theme?.color.grey120,
              padding: Math.ceil(33 * downScalingRatio),
              textAnchor: 'start',
              fontFamily: 'Lato',
            },
            axis: {
              strokeWidth: 0,
            },
          }}
        />
        <VictoryStack colorScale={colorScale}>
          {Object.values(dataset).map((data: any, i: number) => {
            return (
              <VictoryBar
                key={i}
                name={`bar-${i}`}
                data={data}
                labels={() => ''}
                alignment="middle"
                barWidth={10}
                labelComponent={
                  <VictoryTooltip
                    flyoutComponent={
                      <Tooltip
                        y={30}
                        dataset={dataset}
                        upScalingRatio={upScalingRatio}
                        downScalingRatio={downScalingRatio}
                        chartRef={chartContainerRef}
                      />
                    }
                  />
                }
              />
            );
          })}
        </VictoryStack>
      </VictoryChart>
    </div>
  );
};

export {ScoreDistributionChart};
