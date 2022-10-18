import React from 'react';
import styled from 'styled-components';
import {getColor, useTheme} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {TimeToEnrich} from '../models';

const Container = styled('div')`
  margin: 0;
  padding: 0;
  font-size: 10px;
  height: 100%;
  width: 100%;
  border: 1px solid #e8ebee;
  box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  background-color: #ffffff;
`;

const DataContainer = styled('div')`
  padding: 1em 0.5em 0.5em 1em;
  color: ${getColor('grey', 100)};
  font-size: 14px;
`;

const EnrichedProductHighlight = styled('span')`
  font-size: 18px;
  color: ${getColor('grey', 140)};
`;

const EnrichedProductText = styled('div')`
  font-size: 9px;
`;

const tooltipWidthPx = 250;
const tooltipHeightPx = 60;

type Props = {
  x?: number;
  y?: number;
  datum?: {x: string; y: string; _group: number; _stack: number};
  referenceTimeToEnrichList?: TimeToEnrich[];
  comparisonTimeToEnrichList?: TimeToEnrich[];
};

const tooltipShouldBeDisplayedToTheLeft: (
  referenceTimeToEnrichList?: TimeToEnrich[],
  datum?: {x: string; y: string; _group: number; _stack: number}
) => boolean = (referenceTimeToEnrichList, datum) => {
  if (!referenceTimeToEnrichList || !datum) {
    return false;
  }

  const periods = referenceTimeToEnrichList?.map((timeToEnrich: TimeToEnrich) => timeToEnrich.period);

  return periods.indexOf(referenceTimeToEnrichList[datum._group]['period']) > periods.length / 2;
};

const TimeToEnrichHistoricalChartTooltip: React.FC<Props> = ({
  referenceTimeToEnrichList,
  comparisonTimeToEnrichList,
  x,
  y,
  datum,
}) => {
  const translate = useTranslate();
  const theme = useTheme();

  const value =
    typeof datum !== 'undefined' && referenceTimeToEnrichList ? referenceTimeToEnrichList[datum._group]['value'] : 0;
  const comparisonValue =
    typeof datum !== 'undefined' && comparisonTimeToEnrichList ? comparisonTimeToEnrichList[datum._group]['value'] : 0;
  const comparedValue = value - comparisonValue;

  let tooltipX = x ? x + 20 : 0;
  if (tooltipShouldBeDisplayedToTheLeft(referenceTimeToEnrichList, datum)) {
    tooltipX = x ? x - (tooltipWidthPx + 20) : 0;
  }

  return (
    <g style={{pointerEvents: 'none', fill: 'none', stroke: 'none'}}>
      {referenceTimeToEnrichList && <line x1={x} x2={x} y1={0} y2={350} stroke={theme.color.blue100} strokeWidth="3" />}
      <circle cx={x} cy={y} r={7} fill="#ffffff" stroke={theme.color.blue100} strokeWidth={3} />
      {referenceTimeToEnrichList && (
        <foreignObject x={tooltipX} y={y ? y - 30 : 0} width={tooltipWidthPx} height={tooltipHeightPx}>
          <Container>
            <DataContainer>
              <EnrichedProductHighlight>{value}</EnrichedProductHighlight>{' '}
              {translate('akeneo.performance_analytics.graph.tooltip_day_to_enrich')}
              <EnrichedProductText>
                {comparedValue > 0 &&
                  `+${comparedValue} ${translate('akeneo.performance_analytics.graph.tooltip_compared_period')}`}
                {comparedValue < 0 &&
                  `${comparedValue} ${translate('akeneo.performance_analytics.graph.tooltip_compared_period')}`}
                {comparedValue === 0 && translate('akeneo.performance_analytics.graph.tooltip_compared_period_same')}
              </EnrichedProductText>
            </DataContainer>
          </Container>
        </foreignObject>
      )}
    </g>
  );
};

export {TimeToEnrichHistoricalChartTooltip};
