import React, {FunctionComponent} from "react";
import {FlyoutProps} from "victory";

const __ = require('oro/translator');

interface TooltipProps extends FlyoutProps{
  datum?: {x: string, _group: number, _stack: number};
  x?: any;
  y: any;
  data: any;
  upScalingRatio: number,
  downScalingRatio: number,
}

export const calculateAverageGrade = (data: any, datum: any) => {

  const letterRankMap = new Map([
    [1, "E"],
    [2, "D"],
    [3, "C"],
    [4, "B"],
    [5, "A"],
  ]);

  const sumOfRates =
    ((data['rank_1'][datum._group].y) * 5) +
    ((data['rank_2'][datum._group].y) * 4) +
    ((data['rank_3'][datum._group].y) * 3) +
    ((data['rank_4'][datum._group].y) * 2) +
    ((data['rank_5'][datum._group].y));

  const average = Math.round(sumOfRates / 100);

  return letterRankMap.get(average);
};

const Tooltip: FunctionComponent<TooltipProps> = ({datum, x, y, data, upScalingRatio, downScalingRatio}) => {

  if(datum === undefined || datum._stack === 6) {
    return(<></>);
  }

  const averageGrade = calculateAverageGrade(data, datum);

  return (
    <g style={{pointerEvents: 'none', fill: "none", stroke: "none"}} transform={`scale(${downScalingRatio}, ${downScalingRatio})`}>
      <foreignObject x={x*upScalingRatio - 20} y={y} width="300" height="350">
        <div className="AknHoverBoxWithArrow">
          <div className="AknHoverBoxArrow"/>
          <div className="AknHoverBox">
            <div className="AknHoverBox-content">
              <div className="AknHoverBox-title">Distribution</div>
              <ul className="AknMessageBox-list">
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-A">A</span>
                  <span className="rate-value">{Math.round(data['rank_1'][datum._group].y)}%</span>
                </li>
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-B">B</span>
                  <span className="rate-value">{Math.round(data['rank_2'][datum._group].y)}%</span>
                </li>
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-C">C</span>
                  <span className="rate-value">{Math.round(data['rank_3'][datum._group].y)}%</span>
                </li>
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-D">D</span>
                  <span className="rate-value">{Math.round(data['rank_4'][datum._group].y)}%</span>
                </li>
                <li>
                  <span className="AknDataQualityInsightsRate AknDataQualityInsightsRate-E">E</span>
                  <span className="rate-value">{Math.round(data['rank_5'][datum._group].y)}%</span>
                </li>
              </ul>
              <div className="AknHoverBox-footer">
                <span className={`AknDataQualityInsightsRate AknDataQualityInsightsRate-${averageGrade}`}>{averageGrade}</span>
                <span className="rate-value">{__(`akeneo_data_quality_insights.dqi_dashboard.average_grade`)}</span>
              </div>
            </div>
          </div>
        </div>
      </foreignObject>
    </g>
  );
};

export default Tooltip;
