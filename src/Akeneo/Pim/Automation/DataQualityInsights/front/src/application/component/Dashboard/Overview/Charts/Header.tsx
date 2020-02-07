import React, {FunctionComponent} from 'react';

const __ = require('oro/translator');

interface AxisChartHeaderProps {
  axisName: string;
  displayLegend: boolean;
}

const Header: FunctionComponent<AxisChartHeaderProps> = ({axisName, displayLegend}) => {
  return (
    <header className="AknDataQualityOverviewChartHeader">
          <span className="AknSubsection-AxisTitle">{axisName}</span>
          {
            displayLegend && (
              <div className="AknSubsection-ChartLegend">
                <span className="AknBadge AknBadge--small AknBadge--highlight--excellent"/>
                <span className="AknSubsection-legend">{__(`akeneo_data_quality_insights.dqi_dashboard.legend.excellent`)}</span>
                <span className="AknBadge AknBadge--small AknBadge--highlight--good"/>
                <span className="AknSubsection-legend">{__(`akeneo_data_quality_insights.dqi_dashboard.legend.good`)}</span>
                <span className="AknBadge AknBadge--small AknBadge--highlight--average"/>
                <span className="AknSubsection-legend">{__(`akeneo_data_quality_insights.dqi_dashboard.legend.average`)}</span>
                <span className="AknBadge AknBadge--small AknBadge--highlight--below-average"/>
                <span className="AknSubsection-legend">{__(`akeneo_data_quality_insights.dqi_dashboard.legend.below_average`)}</span>
                <span className="AknBadge AknBadge--small AknBadge--highlight--to-improve"/>
                <span className="AknSubsection-legend">{__(`akeneo_data_quality_insights.dqi_dashboard.legend.to_improve`)}</span>
              </div>
            )
          }
    </header>
  );
};

export default Header;
