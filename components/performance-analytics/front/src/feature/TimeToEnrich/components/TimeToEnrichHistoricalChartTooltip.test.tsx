import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {TimeToEnrichHistoricalChartTooltip} from './TimeToEnrichHistoricalChartTooltip';
import {TimeToEnrich} from '../models';

const referenceTimeToEnrichList: TimeToEnrich[] = [
  {code: '2022-W30', value: 10},
  {code: '2022-W31', value: 12},
  {code: '2022-W32', value: 9},
];
const comparisonTimeToEnrichList: TimeToEnrich[] = [
  {code: '2022-W30', value: 15},
  {code: '2022-W31', value: 12},
  {code: '2022-W32', value: 5},
];

describe('TimeToEnrichHistoricalChartTooltip', () => {
  it('renders a tooltip when the TTE is better than previous period', async () => {
    const datum = {_group: 0, x: '10', y: '20', _stack: 12};

    renderWithProviders(
      <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <TimeToEnrichHistoricalChartTooltip
          referenceTimeToEnrichList={referenceTimeToEnrichList}
          comparisonTimeToEnrichList={comparisonTimeToEnrichList}
          datum={datum}
          x={10}
          y={20}
        />
      </svg>
    );

    expect(screen.getByText('akeneo.performance_analytics.graph.tooltip_day_to_enrich')).toBeInTheDocument();
    expect(screen.getByText('-5 akeneo.performance_analytics.graph.tooltip_compared_period')).toBeInTheDocument();
  });

  it('renders a tooltip when the TTE is the same as previous period', async () => {
    const datum = {_group: 1, x: '10', y: '20', _stack: 12};

    renderWithProviders(
      <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <TimeToEnrichHistoricalChartTooltip
          referenceTimeToEnrichList={referenceTimeToEnrichList}
          comparisonTimeToEnrichList={comparisonTimeToEnrichList}
          datum={datum}
          x={10}
          y={20}
        />
      </svg>
    );

    expect(screen.getByText('akeneo.performance_analytics.graph.tooltip_day_to_enrich')).toBeInTheDocument();
    expect(screen.getByText('akeneo.performance_analytics.graph.tooltip_compared_period_same')).toBeInTheDocument();
  });

  it('renders a tooltip when the TTE is worst than previous period', async () => {
    const datum = {_group: 2, x: '10', y: '20', _stack: 12};

    renderWithProviders(
      <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <TimeToEnrichHistoricalChartTooltip
          referenceTimeToEnrichList={referenceTimeToEnrichList}
          comparisonTimeToEnrichList={comparisonTimeToEnrichList}
          datum={datum}
          x={10}
          y={20}
        />
      </svg>
    );

    expect(screen.getByText('akeneo.performance_analytics.graph.tooltip_day_to_enrich')).toBeInTheDocument();
    expect(screen.getByText('+4 akeneo.performance_analytics.graph.tooltip_compared_period')).toBeInTheDocument();
  });

  it('renders nothing when props are not given', async () => {
    renderWithProviders(
      <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <TimeToEnrichHistoricalChartTooltip />
      </svg>
    );

    expect(screen.queryByText('akeneo.performance_analytics.graph.tooltip_day_to_enrich')).not.toBeInTheDocument();
  });
});
