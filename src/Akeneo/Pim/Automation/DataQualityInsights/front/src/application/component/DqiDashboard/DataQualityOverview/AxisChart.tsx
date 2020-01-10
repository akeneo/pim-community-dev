import React from "react";
import {VictoryAxis, VictoryBar, VictoryChart, VictoryStack, VictoryTooltip} from "victory";
import {RANK_1_COLOR, RANK_2_COLOR, RANK_3_COLOR, RANK_4_COLOR, RANK_5_COLOR, NO_RATE_COLOR} from "../../../../domain";
import Tooltip from "./Tooltip";

interface AxisChartProps {
  dataset: any;
  padding: number;
  barRatio: number;
  dateFormatCallback: {(date: string): string};
}

const AxisChart = ({dataset, padding, barRatio, dateFormatCallback}: AxisChartProps) => {

  let dates: any[] = [];
  if(Object.entries(dataset).length > 0) {
    dates = Object.values(dataset['rank_1']).map((rate: any) => rate.x);
  }

  return (
    <VictoryChart
       height={268}
       padding={{top: 0, bottom: 65, left: padding, right: padding}}
       width={1000}
    >
      <VictoryStack
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
            labelComponent={ <VictoryTooltip flyoutComponent={<Tooltip y={30} data={dataset}/>}/> }
          />;
        })}
      </VictoryStack>
      <VictoryAxis
        tickValues={dates}
        tickFormat={dateFormatCallback}
        style={{
          axis: {stroke: "none"},
          tickLabels: {fontSize: 11, fill: "#67768a", padding: 27}
        }}
      />
    </VictoryChart>
  )
};

export default AxisChart;
