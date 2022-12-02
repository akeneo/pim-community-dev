import React, {useEffect, useState, useRef} from 'react';
import {
  VictoryChart,
  VictoryArea,
  VictoryAxis,
  VictoryGroup,
  VictoryTheme,
  VictoryVoronoiContainer,
  InterpolationPropType,
  VictoryTooltip,
} from 'victory';
import {TimeToEnrich} from '../models';
import {TimeToEnrichHistoricalChartTooltip} from './TimeToEnrichHistoricalChartTooltip';
import {useTheme} from 'akeneo-design-system';

const interpolation: InterpolationPropType = 'catmullRom';

type TimeToEnrichHistoricalChartProps = {
  referenceTimeToEnrichList: TimeToEnrich[];
  comparisonTimeToEnrichList?: TimeToEnrich[];
};

// To avoid area chart line cropping, add a padding to the top of the chart
const getMaxY = (referenceTimeToEnrichList: TimeToEnrich[], comparisonTimeToEnrichList: TimeToEnrich[] | undefined) => {
  const maxLimit = 0.2;
  const lists = comparisonTimeToEnrichList
    ? [...referenceTimeToEnrichList, ...comparisonTimeToEnrichList]
    : referenceTimeToEnrichList;
  const maxValue = Math.max(...lists.map(d => d.value));
  return maxValue + maxValue * maxLimit;
};

let tempo: ReturnType<typeof setTimeout> | undefined = undefined;

const TimeToEnrichHistoricalChart = ({
  referenceTimeToEnrichList,
  comparisonTimeToEnrichList,
}: TimeToEnrichHistoricalChartProps) => {
  const [width, setWidth] = useState(0);
  const ref = useRef<HTMLInputElement>(null);
  const theme = useTheme();
  const chartRedrawTempo = 50;

  useEffect(() => {
    // Make effective the chart width change
    const doResize = () => {
      const newWidth = ref.current?.offsetWidth ? ref.current?.offsetWidth : 0;
      setWidth(newWidth);
    };
    // Temporize the chart width change
    const handleResize = () => {
      if (typeof tempo !== 'undefined') {
        clearTimeout(tempo);
      }

      tempo = setTimeout(() => {
        doResize();
      }, chartRedrawTempo);
    };
    // Add event listener
    window.addEventListener('resize', handleResize);
    handleResize();

    // Be sure to remove the event listener when the component is unmounted
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  return (
    <div style={{width: '100%', height: '400px', position: 'relative'}} ref={ref}>
      <svg style={{position: 'absolute'}}>
        <defs>
          <linearGradient id="myGradient" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stopColor={theme.color.blue100} />
            <stop offset="100%" stopColor="white" />
          </linearGradient>
        </defs>
      </svg>
      <div style={{position: 'absolute', overflow: 'visible'}}>
        <VictoryChart
          height={400}
          width={width}
          theme={VictoryTheme.material}
          padding={{top: 0, bottom: 50, left: 30, right: 5}}
          maxDomain={{y: getMaxY(referenceTimeToEnrichList, comparisonTimeToEnrichList)}}
          containerComponent={<VictoryVoronoiContainer voronoiDimension="x" />}
        >
          <VictoryAxis />
          <VictoryAxis dependentAxis />

          <VictoryGroup style={{data: {stroke: '#3c86b3', strokeWidth: 3}}}>
            <VictoryArea
              data={referenceTimeToEnrichList}
              x="code"
              y="value"
              style={{
                data: {fill: 'url(#myGradient)', fillOpacity: 0.6},
              }}
              interpolation={interpolation}
              labels={() => ''}
              labelComponent={
                <VictoryTooltip
                  flyoutComponent={
                    <TimeToEnrichHistoricalChartTooltip
                      referenceTimeToEnrichList={referenceTimeToEnrichList}
                      comparisonTimeToEnrichList={comparisonTimeToEnrichList}
                    />
                  }
                />
              }
            />
            {comparisonTimeToEnrichList && (
              <VictoryArea
                data={comparisonTimeToEnrichList}
                x="code"
                y="value"
                style={{
                  data: {fill: 'white', fillOpacity: 0, strokeDasharray: '6, 8'},
                }}
                interpolation={interpolation}
                labels={() => ''}
                labelComponent={<VictoryTooltip flyoutComponent={<TimeToEnrichHistoricalChartTooltip />} />}
              />
            )}
          </VictoryGroup>
        </VictoryChart>
      </div>
    </div>
  );
};

export {TimeToEnrichHistoricalChart};
