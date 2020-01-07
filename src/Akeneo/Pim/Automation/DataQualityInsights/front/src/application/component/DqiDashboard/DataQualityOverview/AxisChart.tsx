import React, {useState} from "react";
import {VictoryAxis, VictoryBar, VictoryChart, VictoryStack, VictoryContainer} from "victory";

type RankDistribution = {
  x: string;
  y: number;
}

type Dataset = {
  [rank: string]: RankDistribution;
}

interface AxisChartProps {
  dataset: Dataset;
}

const AxisChart = ({dataset}: AxisChartProps) => {

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
          "rgb(169, 76, 63)",
          "rgb(212, 96, 79)",
          "rgb(249, 181, 63)",
          "rgb(103, 179, 115)",
          "rgb(82, 143, 92)",
          "rgb(217, 221, 226)"
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
                        // console.log(isVisible)
                      }
                    }
                  },
                  onMouseLeave: () => {
                    return {
                      mutation: () => {
                        setIsVisible(false);
                        // console.log(isVisible)
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

export default AxisChart;
