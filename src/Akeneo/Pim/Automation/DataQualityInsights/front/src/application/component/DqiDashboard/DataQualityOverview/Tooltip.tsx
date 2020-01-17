import React, {FunctionComponent} from "react";
import {FlyoutProps} from "victory";

interface TooltipProps extends FlyoutProps{
  datum?: {x: string, _group: number, _stack: number};
  x?: any;
  y: any;
  data: any;
  upScalingRatio: number,
  downScalingRatio: number,
}

const Tooltip: FunctionComponent<TooltipProps> = ({datum, x, y, data, upScalingRatio, downScalingRatio}) => {

  if(datum === undefined || datum._stack === 6) {
    return(<></>);
  }

  return (
    <g style={{pointerEvents: 'none', fill: "none", stroke: "none"}} transform={`scale(${downScalingRatio}, ${downScalingRatio})`}>
      <foreignObject x={x*upScalingRatio - 20} y={y} width="150" height="238">
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
            </div>
          </div>
        </div>
      </foreignObject>
    </g>
  );
};

export default Tooltip;
