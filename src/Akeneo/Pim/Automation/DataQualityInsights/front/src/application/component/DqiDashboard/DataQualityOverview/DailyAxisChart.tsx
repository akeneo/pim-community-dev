import React, {useState} from "react";
import {VictoryAxis, VictoryBar, VictoryChart, VictoryStack} from "victory";
import {RANK_1_COLOR, RANK_2_COLOR, RANK_3_COLOR, RANK_4_COLOR, RANK_5_COLOR, NO_RATE_COLOR} from "../../../../domain";

interface AxisChartProps {
  dataset: any;
}

const DailyAxisChart = ({dataset}: AxisChartProps) => {

  const [isVisible, setIsVisible] = useState(false);

  let days: any[] = [];
  if(Object.entries(dataset).length > 0) {
    days = Object.values(dataset['rank_1']).map((rate: any) => rate.x);
  }
  let i = 0;

  return (
    <VictoryChart
       height={268}
       padding={{top: 0, bottom: 65, left: 71, right: 71}}
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
        {Object.values(dataset).map((data: any) => {
          i++;
          return <VictoryBar
            name={`bar-${i}`}
            data={data} key={i}
            barRatio={1.49}
            labels={() => ""}
            alignment="middle"
            events={[
              {
                target: "data",
                eventHandlers: {
                  onMouseEnter: () => {
                    return {
                      mutation: () => {
                        setIsVisible(true);
                         console.log(isVisible)
                      }
                    }
                  },
                  onMouseLeave: () => {
                    return {
                      mutation: () => {
                        setIsVisible(false);
                         console.log(isVisible)
                      }
                    }
                  }
                }
              }
            ]}
          />;
        })}
      </VictoryStack>
      <VictoryAxis
        tickFormat={days}
        style={{
          axis: {stroke: "none"},
          tickLabels: {fontSize: 11, fill: "#67768a", padding: 27}
        }}
      />
    </VictoryChart>
  )
};

export default DailyAxisChart;
