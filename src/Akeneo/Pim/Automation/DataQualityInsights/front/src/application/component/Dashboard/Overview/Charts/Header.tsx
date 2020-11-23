import React, {FunctionComponent} from 'react';
import styled, {css} from 'styled-components';

const __ = require('oro/translator');

type BulletProps = {
  color?: string;
};
const Bullet = styled.span<BulletProps>(
  ({theme, color = 'grey80'}) => css`
    display: inline-block;
    width: 8px;
    height: 8px;
    margin: 0 8px 0 5px;
    border: none;
    border-radius: 8px;
    background: ${() => theme.color[color]};
  `
);

const Legend = styled.span`
  height: 13px;
  margin-right: 10px;
  color: ${({theme}) => theme.color.grey140};
  font-size: ${({theme}) => theme.fontSize.small};
`;

interface AxisChartHeaderProps {
  axisName: string;
  displayLegend: boolean;
}

const Header: FunctionComponent<AxisChartHeaderProps> = ({axisName, displayLegend}) => {
  return (
    <header className="AknDataQualityOverviewChartHeader">
      <span className="AknSubsection-AxisTitle">{axisName}</span>
      {displayLegend && (
        <div className="AknSubsection-ChartLegend">
          <Legend>
            <Bullet color={'green60'} />
            {__(`akeneo_data_quality_insights.dqi_dashboard.legend.excellent`)}
          </Legend>
          <Legend>
            <Bullet color={'green100'} />
            {__(`akeneo_data_quality_insights.dqi_dashboard.legend.good`)}
          </Legend>
          <Legend>
            <Bullet color={'yellow60'} />
            {__(`akeneo_data_quality_insights.dqi_dashboard.legend.average`)}
          </Legend>
          <Legend>
            <Bullet color={'red60'} />
            {__(`akeneo_data_quality_insights.dqi_dashboard.legend.below_average`)}
          </Legend>
          <Legend>
            <Bullet color={'red100'} />
            {__(`akeneo_data_quality_insights.dqi_dashboard.legend.to_improve`)}
          </Legend>
        </div>
      )}
    </header>
  );
};

export default Header;
