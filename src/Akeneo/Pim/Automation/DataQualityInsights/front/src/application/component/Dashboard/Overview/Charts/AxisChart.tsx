import React, {useRef} from "react";
import {
  VictoryAxis,
  VictoryBar,
  VictoryChart,
  VictoryStack,
  VictoryTooltip
} from "victory";
import {NO_RATE_COLOR, RANK_1_COLOR, RANK_2_COLOR, RANK_3_COLOR, RANK_4_COLOR, RANK_5_COLOR} from "../../../../../domain";
import {useGetDashboardChartScalingSizeRatio} from "../../../../../infrastructure/hooks";
import Tooltip from "./Tooltip";

const INITIAL_CHART_WIDTH = 1000;
const INITIAL_CHART_HEIGHT = 268;

interface AxisChartProps {
  dataset: any;
  padding: number;
  barRatio: number;
  dateFormatCallback: {(date: string, index:number): string};
}

const AxisChart = ({dataset, padding, barRatio, dateFormatCallback}: AxisChartProps) => {
 const chartContainerRef = useRef<HTMLDivElement|null>(null);
  const {upScalingRatio, downScalingRatio} = useGetDashboardChartScalingSizeRatio(chartContainerRef, INITIAL_CHART_WIDTH);

  let dates: any[] = [];
  if(Object.entries(dataset).length > 0) {
    dates = Object.values(dataset['rank_1']).map((rate: any) => rate.x);
  }

  return (
    <div ref={chartContainerRef}>
      <VictoryChart
        height={INITIAL_CHART_HEIGHT}
        width={INITIAL_CHART_WIDTH}
        padding={{top: 0, bottom: 65, left: padding, right: padding}}
      >
        <VictoryAxis
          tickValues={dates}
          tickFormat={dateFormatCallback}
          style={{
            axis: {strokeWidth: 0},
            tickLabels: {
              fontSize: Math.ceil(11 * (downScalingRatio)),
              fill: "#67768a",
              padding: Math.ceil(27 * (downScalingRatio)),
              fontFamily: "Lato",
              textTransform: "capitalize"
            }
          }}
        />
        <VictoryAxis
          dependentAxis
          domain={[0, 100]}
          orientation="left"
          standalone={false}
          tickValues={[0, 33, 66, 100]}
          style={{
            grid: {
              stroke: "#e8ebee",
              strokeWidth: 1,

            },
            tickLabels: {
              fontSize: 0
            },
            axis: {
              strokeWidth: 0
            }
          }}
        />
        <VictoryStack
          domain={{y: [0, 100] }}
          colorScale={[
            `${RANK_5_COLOR}`,
            `${RANK_4_COLOR}`,
            `${RANK_3_COLOR}`,
            `${RANK_2_COLOR}`,
            `${RANK_1_COLOR}`,
            `${NO_RATE_COLOR}`
          ]}
        >
          {Object.values(dataset).map((data: any, i: number) => {

            return <VictoryBar
              key={i}
              name={`bar-${i}`}
              data={data}
              barRatio={barRatio}
              labels={() => ""}
              alignment="middle"
              labelComponent={ <VictoryTooltip flyoutComponent={<Tooltip y={30} data={dataset} upScalingRatio={upScalingRatio} downScalingRatio={downScalingRatio}/>}/> }
            />;
          })}
        </VictoryStack>
      </VictoryChart>
    </div>
  )
};

export default AxisChart;
