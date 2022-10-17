import React, {useEffect, useState, useRef} from 'react';
import {
  VictoryChart,
  VictoryArea,
  VictoryLine,
  VictoryAxis,
  VictoryGroup,
  VictoryTheme,
  VictoryVoronoiContainer,
  InterpolationPropType,
} from 'victory';
import {TimeToEnrich} from '../models';

const interpolation: InterpolationPropType = 'catmullRom';

type TimeToEnrichHistoricalChartProps = {
  referenceTimeToEnrichList: TimeToEnrich[];
  comparisonTimeToEnrichList?: TimeToEnrich[];
};

// To avoid area chart line cropping, add a padding to the top of the chart
const getMaxY = (data: TimeToEnrich[]) => {
  return Math.max(...data.map(d => d.value)) + 10;
};

let tempo: ReturnType<typeof setTimeout> | undefined = undefined;

const TimeToEnrichHistoricalChart = ({
  referenceTimeToEnrichList,
  comparisonTimeToEnrichList,
}: TimeToEnrichHistoricalChartProps) => {
  const [width, setWidth] = useState(0);
  const ref = useRef<HTMLInputElement>(null);
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
    <div style={{width: '100%', height: '400px', position: 'relative', overflow: 'hidden'}} ref={ref}>
      <svg style={{position: 'absolute'}}>
        <defs>
          <linearGradient id="myGradient" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stopColor="#5992C7" />
            <stop offset="100%" stopColor="white" />
          </linearGradient>
        </defs>
      </svg>
      <div style={{position: 'absolute'}}>
        <VictoryChart
          height={400}
          width={width}
          theme={VictoryTheme.material}
          containerComponent={<VictoryVoronoiContainer voronoiDimension="x" />}
        >
          <VictoryAxis />
          <VictoryAxis dependentAxis />

          <VictoryGroup
            style={{
              data: {strokeWidth: 3, fillOpacity: 0.6},
            }}
          >
            <VictoryLine
              style={{
                data: {stroke: '#11324D'},
              }}
              y={() => getMaxY(referenceTimeToEnrichList)}
              x={width}
            />
            <VictoryArea
              data={referenceTimeToEnrichList}
              x="period"
              y="value"
              style={{
                data: {fill: 'url(#myGradient)', stroke: '#3c86b3'},
              }}
              interpolation={interpolation}
            />
          </VictoryGroup>
          {comparisonTimeToEnrichList && (
            <VictoryGroup
              style={{
                data: {strokeWidth: 3, fillOpacity: 0, strokeDasharray: '6, 8'},
              }}
            >
              <VictoryArea
                data={comparisonTimeToEnrichList}
                x="period"
                y="value"
                style={{
                  data: {fill: 'white', stroke: '#3c86b3'},
                }}
                interpolation={interpolation}
              />
            </VictoryGroup>
          )}
        </VictoryChart>
      </div>
    </div>
  );
};

export {TimeToEnrichHistoricalChart};
