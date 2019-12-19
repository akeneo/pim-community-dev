import React, { FunctionComponent, useState } from 'react';
import {VictoryBar, VictoryChart, VictoryAxis, VictoryStack} from 'victory';
import {useFetchDqiDashboardData} from "../../../../infrastructure/hooks";

const transformData = (dataset: any[]) => {

  if (dataset.length < 1) {
    return [];
  }

  const totals = dataset[0].map((data: any, i:number) => {
    console.log(data);
    return dataset.reduce((memo: any, curr: any) => {
      return memo + curr[i].y;
    }, 0);
  });

  return dataset.map((data: any) => {
    return data.map((datum: any, i: any) => {
      return { x: datum.x, y: (datum.y / totals[i]) * 100 };
    });
  });
};




interface DataQualityOverviewChartProps {
  catalogLocale: string;
  catalogChannel: string;
}

const DataQualityOverviewChart: FunctionComponent<DataQualityOverviewChartProps> = ({catalogChannel, catalogLocale}) => {
  const myDataset = useFetchDqiDashboardData(catalogChannel, catalogLocale);
  const [isVisible, setIsVisible] = useState(false);
  const dataset = transformData(myDataset);

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
            {dataset.map((data: any, i: any) => {
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
            tickFormat={["day1", "day2", "day3", "day4", "day5", "day6", "day7"]}
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
