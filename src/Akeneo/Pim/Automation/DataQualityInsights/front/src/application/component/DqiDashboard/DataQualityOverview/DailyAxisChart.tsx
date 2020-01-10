import React, {FunctionComponent} from "react";
import {VictoryAxis, VictoryBar, VictoryChart, VictoryStack, VictoryTooltip, FlyoutProps} from "victory";
import {RANK_1_COLOR, RANK_2_COLOR, RANK_3_COLOR, RANK_4_COLOR, RANK_5_COLOR, NO_RATE_COLOR} from "../../../../domain";

interface DailyAxisChartProps {
  dataset: any;
}

interface GraphTooltipProps extends FlyoutProps{
  datum?: {x: string, _group: number, _stack: number};
  x?: any;
  y: any;
  data: any;
}

export const GraphTooltip: FunctionComponent<GraphTooltipProps> = ({datum, x, y, data}) => {

  if(datum === undefined || datum._stack === 6) {
    return(<></>);
  }

  return (
    <g style={{pointerEvents: 'none', fill: "none", stroke: "none"}} transform="scale(0.714, 0.714)">
      <foreignObject x={x*1.4 - 20} y={y} width="150" height="238">
        <div className="AknHoverBoxWithArrow">
          <div className="AknHoverBoxArrow"/>
          <div className="AknHoverBox">
            <div className="AknHoverBox-content">
              <div className="AknHoverBox-title">Distribution</div>
              <ul className="AknMessageBox-list">
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-A">A</span>
                  <span className="rate-value">{data['rank_1'][datum._group].y}%</span>
                </li>
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-B">B</span>
                  <span className="rate-value">{data['rank_2'][datum._group].y}%</span>
                </li>
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-C">C</span>
                  <span className="rate-value">{data['rank_3'][datum._group].y}%</span>
                </li>
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-D">D</span>
                  <span className="rate-value">{data['rank_4'][datum._group].y}%</span>
                </li>
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-E">E</span>
                  <span className="rate-value">{data['rank_5'][datum._group].y}%</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </foreignObject>
    </g>
  );
};

const DailyAxisChart = ({dataset}: DailyAxisChartProps) => {

  let days: any[] = [];
  if(Object.entries(dataset).length > 0) {
    days = Object.values(dataset['rank_1']).map((rate: any) => rate.x);
  }

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
        {Object.values(dataset).map((data: any, i: number) => {

          return <VictoryBar
            name={`bar-${i}`}
            data={data} key={i}
            barRatio={1.49}
            labels={() => ""}
            alignment="middle"
            labelComponent={ <VictoryTooltip flyoutComponent={<GraphTooltip y={30} data={dataset}/>}/> }
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
