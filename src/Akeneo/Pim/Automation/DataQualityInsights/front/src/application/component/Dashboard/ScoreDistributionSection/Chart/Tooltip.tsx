import React, {FC, RefObject} from 'react';
import {FlyoutProps} from 'victory';
import {ScoreDistributionChartDataset} from '../../../../../domain';
import {Summary} from './Summary';

type Props = FlyoutProps & {
  datum?: {x: string; _group: number; _stack: number};
  x?: any;
  y: any;
  dataset: ScoreDistributionChartDataset;
  upScalingRatio: number;
  downScalingRatio: number;
  chartRef: RefObject<HTMLElement>;
};

const SUMMARY_CONTAINER_WIDTH = 190;
const SUMMARY_CONTAINER_HEIGHT = 350;

const calculateAverageGrade = (data: any, datum: any) => {
  const letterRankMap = new Map([
    [1, 'E'],
    [2, 'D'],
    [3, 'C'],
    [4, 'B'],
    [5, 'A'],
  ]);

  const sumOfRates =
    data['rank_1'][datum._group].y * 5 +
    data['rank_2'][datum._group].y * 4 +
    data['rank_3'][datum._group].y * 3 +
    data['rank_4'][datum._group].y * 2 +
    data['rank_5'][datum._group].y;

  const average = Math.round(sumOfRates / 100);

  return letterRankMap.get(average);
};

const Tooltip: FC<Props> = ({datum, x, y, dataset, upScalingRatio, downScalingRatio, chartRef}) => {
  if (datum === undefined || datum._stack === 6) {
    return <></>;
  }

  const margin = 30;
  const oversize = chartRef.current !== null && SUMMARY_CONTAINER_WIDTH + x + margin >= chartRef.current.clientWidth;
  const averageGrade = calculateAverageGrade(dataset, datum);
  const positionX = oversize ? x * upScalingRatio - SUMMARY_CONTAINER_WIDTH : x * upScalingRatio;

  return (
    <g
      style={{pointerEvents: 'none', fill: 'none', stroke: 'none'}}
      transform={`scale(${downScalingRatio}, ${downScalingRatio})`}
    >
      <foreignObject x={positionX} y={y} width={SUMMARY_CONTAINER_WIDTH} height={SUMMARY_CONTAINER_HEIGHT}>
        <Summary
          totalA={dataset['rank_1'][datum._group].y}
          totalB={dataset['rank_2'][datum._group].y}
          totalC={dataset['rank_3'][datum._group].y}
          totalD={dataset['rank_4'][datum._group].y}
          totalE={dataset['rank_5'][datum._group].y}
          averageScore={averageGrade || null}
          flip={oversize}
        />
      </foreignObject>
    </g>
  );
};

export {Tooltip, calculateAverageGrade};
