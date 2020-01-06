import React, { useState } from 'react';
import {VictoryBar, VictoryChart, VictoryAxis, VictoryStack} from 'victory';
import {useFetchDqiDashboardData} from "../../../../infrastructure/hooks";

type Ranks = {
  [rank: string]: number;
}

type AxisRates = {
  [date: string]: Ranks;
};

type Dataset = {
  [axisName: string]: AxisRates;
};

const transformData = (dataset: Dataset, axisName: string) => {
  if (Object.keys(dataset).length === 0) {
    return {};
  }

  let ranks: {[rank: string]: any[]} = {
    'rank_1': [],
    'rank_2': [],
    'rank_3': [],
    'rank_4': [],
    'rank_5': [],
  };

  Object.entries(dataset[axisName]).map(([date, ranksByDay]) => {
    Object.entries(ranksByDay).map(([rank, distribution]) => {
      ranks[rank].push({x: date, y: distribution});
    });
  });

  return ranks;
};

interface DataQualityOverviewChartProps {
  catalogLocale: string;
  catalogChannel: string;
}

const DataQualityOverviewChart = ({catalogChannel, catalogLocale}: DataQualityOverviewChartProps) => {
  const myDataset = useFetchDqiDashboardData(catalogChannel, catalogLocale);
  const [isVisible, setIsVisible] = useState(false);
  const dataset = transformData(myDataset, 'consistency');

  let days: any[] = [];
  if(Object.entries(dataset).length > 0) {
    days = Object.values(dataset['rank_1']).map((rate: any) => rate.x);
  }

  let i = 0;

  if (Object.entries(dataset).length === 0) {
    return (<></>);
  }

  return (
    <div className='AknDataQualityInsights-chart'>
        <VictoryChart
          height={264}
          width={1000}
          // containerComponent={<VictoryContainer responsive={true}/>}
        >
          <VictoryStack
            colorScale={[
              "rgb(169, 76, 63)",
              "rgb(212, 96, 79)",
              "rgb(249, 181, 63)",
              "rgb(103, 179, 115)",
              "rgb(82, 143, 92)"
            ]}
          >
            {Object.values(dataset).map((data: any) => {
              i++;
              return <VictoryBar
                name={`bar-${i}`}
                data={data} key={i}
                barRatio={1.49}
                labels={() => ""}
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
            style={{ axis: {stroke: "none"} }}
          />
        </VictoryChart>
        {/*{isVisible && (<CustomTooltip/>)}*/}
      </div>
    );
  };

export default DataQualityOverviewChart;




/*
labels={ () => ""}
labelComponent=
    {<VictoryTooltip
    cornerRadius={ 0 }
    centerOffset={{ x: 2000 }}
    height={100}
    width={70}
    style={{ fill: "rgb(255, 255, 255)"}}
    pointerOrientation="left"
    dy={12}
    dx={0}
    pointerWidth={10}
    pointerLength={5}
    flyoutStyle={{
        stroke: "none",
        boxShadow: "0px 0px 4px 0px rgba(0, 0, 0, 0.3)"
    }}
    horizontal={true}
/>
}
*/

/*
class CustomFlyout extends React.Component {
    render() {
        const {x, y, orientation} = this.props;
        const newY = orientation === "bottom" ? y - 35 : y + 35;
        return (
            <g>
                <circle cx={x} cy={newY} r="20" stroke="tomato" fill="none"/>
                <circle cx={x} cy={newY} r="25" stroke="orange" fill="none"/>
                <circle cx={x} cy={newY} r="30" stroke="gold" fill="none"/>
            </g>
        );
    }
}

class App extends React.Component {
    render() {
        return (
            <VictoryChart
                domain={{ x: [0, 11], y: [-10, 10] }}
            >
                <VictoryBar
                    labelComponent={
                        <VictoryTooltip
                            flyoutComponent={<CustomFlyout/>}
                        />
                    }
                    data={[
                        {x: 2, y: 5, label: "A"},
                        {x: 4, y: -6, label: "B"},
                        {x: 6, y: 4, label: "C"},
                        {x: 8, y: -5, label: "D"},
                        {x: 10, y: 7, label: "E"}
                    ]}
                    style={{
                        data: {fill: "tomato", width: 20},
                        labels: { fill: "tomato"}
                    }}
                />
            </VictoryChart>
        );
    }
}
ReactDOM.render(<App/>, mountNode);
*/
