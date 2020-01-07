import React, {FunctionComponent} from 'react';

interface AxisChartHeaderProps {
  axisName: string;
  displayLegend: boolean;
}

const DataQualityOverviewChartHeader: FunctionComponent<AxisChartHeaderProps> = ({axisName, displayLegend}) => {
  return (
    <header className="AknDataQualityOverviewChartHeader">
          <span className="AknSubsection-AxisTitle">{axisName}</span>
          {
            displayLegend && (
              <div className="AknSubsection-ChartLegend">
                <span className="AknBadge AknBadge--small AknBadge--highlight--excellent"></span>
                <span className="AknSubsection-legend">Excellent</span>
                <span className="AknBadge AknBadge--small AknBadge--highlight--good"></span>
                <span className="AknSubsection-legend">Good</span>
                <span className="AknBadge AknBadge--small AknBadge--highlight--average"></span>
                <span className="AknSubsection-legend">Average</span>
                <span className="AknBadge AknBadge--small AknBadge--highlight--below-average"></span>
                <span className="AknSubsection-legend">Below average</span>
                <span className="AknBadge AknBadge--small AknBadge--highlight--to-improve"></span>
                <span className="AknSubsection-legend">To improve</span>
              </div>
            )
          }
    </header>
  );
};

export default DataQualityOverviewChartHeader;
