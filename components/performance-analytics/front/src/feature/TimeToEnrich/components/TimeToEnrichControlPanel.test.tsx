import React from 'react';
import {renderWithProviders} from '../../tests';
import {act, fireEvent, screen} from '@testing-library/react';
import {TimeToEnrichControlPanel} from './TimeToEnrichControlPanel';
import {Aggregation, Metric, PredefinedComparison, PredefinedPeriod} from '../../Common';
import userEvent from '@testing-library/user-event';

describe('TimeToEnrichControlPanel', () => {
  it('displays the control panel', async () => {
    await renderWithProviders(
      <TimeToEnrichControlPanel
        isOpen={true}
        filters={{
          metric: Metric.TIME_TO_ENRICH,
          period: PredefinedPeriod.LAST_12_WEEKS,
          aggregation: Aggregation.FAMILIES,
          comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
          families: [],
          channels: [],
          locales: [],
        }}
        onFiltersChange={() => {}}
        onIsControlPanelOpenChange={() => {}}
      />
    );

    expect(screen.getByText('akeneo.performance_analytics.control_panel.title')).toBeInTheDocument();
  });

  it('hides the control panel', async () => {
    const handleIsControlPanelOpenChange = jest.fn();

    await renderWithProviders(
      <TimeToEnrichControlPanel
        isOpen={true}
        filters={{
          metric: Metric.TIME_TO_ENRICH,
          period: PredefinedPeriod.LAST_12_WEEKS,
          aggregation: Aggregation.FAMILIES,
          comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
          families: [],
          channels: [],
          locales: [],
        }}
        onFiltersChange={() => {}}
        onIsControlPanelOpenChange={handleIsControlPanelOpenChange}
      />
    );

    const closeControlPanelButton = screen.getByTestId('close-control-panel');
    act(() => {
      userEvent.click(closeControlPanelButton);
    });

    expect(handleIsControlPanelOpenChange).toHaveBeenCalledWith(false);
  });

  it('displays an aggregation dropdown when the selection aggregation is families', async () => {
    const handleFiltersChange = jest.fn();

    await renderWithProviders(
      <TimeToEnrichControlPanel
        isOpen={true}
        filters={{
          metric: Metric.TIME_TO_ENRICH,
          period: PredefinedPeriod.LAST_12_WEEKS,
          aggregation: Aggregation.FAMILIES,
          comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
          families: [],
          channels: [],
          locales: [],
        }}
        onFiltersChange={handleFiltersChange}
        onIsControlPanelOpenChange={() => {}}
      />
    );

    expect(
      screen.queryByText('akeneo.performance_analytics.control_panel.select_input.aggregations.' + Aggregation.FAMILIES)
    ).toBeInTheDocument();
  });

  it('calls onChange handler on select input', async () => {
    const handleFiltersChange = jest.fn();

    await renderWithProviders(
      <TimeToEnrichControlPanel
        isOpen={true}
        filters={{
          metric: Metric.TIME_TO_ENRICH,
          period: PredefinedPeriod.LAST_12_WEEKS,
          aggregation: Aggregation.FAMILIES,
          comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
          families: [],
          channels: [],
          locales: [],
        }}
        onFiltersChange={handleFiltersChange}
        onIsControlPanelOpenChange={() => {}}
      />
    );

    const [metricInput, aggregationInput] = await screen.findAllByRole('textbox');
    expect(metricInput).toBeInTheDocument();
    expect(aggregationInput).toBeInTheDocument();

    act(() => {
      fireEvent.click(aggregationInput);
    });

    act(() => {
      fireEvent.click(
        screen.getByText(
          'akeneo.performance_analytics.control_panel.select_input.aggregations.' + Aggregation.CATEGORIES
        )
      );
    });

    expect(handleFiltersChange).toHaveBeenCalledWith({
      metric: Metric.TIME_TO_ENRICH,
      period: PredefinedPeriod.LAST_12_WEEKS,
      aggregation: Aggregation.CATEGORIES,
      comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
      families: [],
      channels: [],
      locales: [],
    });
  });

  it('selects options on multi select input', async () => {
    await renderWithProviders(
      <TimeToEnrichControlPanel
        isOpen={true}
        filters={{
          metric: Metric.TIME_TO_ENRICH,
          period: PredefinedPeriod.LAST_12_WEEKS,
          aggregation: Aggregation.FAMILIES,
          comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
          families: [],
          channels: [],
          locales: [],
        }}
        onFiltersChange={() => {}}
        onIsControlPanelOpenChange={() => {}}
      />
    );

    const buttons = await screen.findAllByTitle('pim_common.open');

    buttons.forEach(button => {
      userEvent.click(button);
    });

    userEvent.click(await screen.findByText('[ecommerce]'));
    userEvent.click(await screen.findByText('French'));

    expect(await screen.findByText('[ecommerce]')).toBeInTheDocument();
    expect(await screen.findByText('French')).toBeInTheDocument();
  });
});
