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
      />
    );

    expect(screen.getByText('akeneo.performance_analytics.control_panel.title')).toBeInTheDocument();
  });

  it('hides the control panel', async () => {
    await renderWithProviders(
      <TimeToEnrichControlPanel
        isOpen={false}
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
      />
    );
    expect(screen.queryByText('akeneo.performance_analytics.control_panel.title')).not.toBeInTheDocument();
  });

  it('validates the control panel filters', async () => {
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
      />
    );
    expect(screen.queryByText('akeneo.performance_analytics.control_panel.title')).toBeInTheDocument();

    const validateFiltersButton = screen.getByTestId('validate-filters');
    act(() => {
      userEvent.click(validateFiltersButton);
    });

    expect(handleFiltersChange).toHaveBeenCalledWith({
      metric: Metric.TIME_TO_ENRICH,
      period: PredefinedPeriod.LAST_12_WEEKS,
      aggregation: Aggregation.FAMILIES,
      comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
      families: [],
      channels: [],
      locales: [],
    });
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
      />
    );

    const [metricInput, aggregationInput, periodInput, comparisonInput] = await screen.findAllByRole('textbox');
    expect(metricInput).toBeInTheDocument();
    expect(aggregationInput).toBeInTheDocument();

    fireEvent.click(aggregationInput);
    userEvent.click(
      screen.getByText('akeneo.performance_analytics.control_panel.select_input.aggregations.' + Aggregation.CATEGORIES)
    );

    fireEvent.click(periodInput);
    userEvent.click(
      screen.getByText(
        'akeneo.performance_analytics.control_panel.select_input.periods.' + PredefinedPeriod.LAST_12_MONTHS
      )
    );

    fireEvent.click(comparisonInput);
    userEvent.click(
      screen.getByText(
        'akeneo.performance_analytics.control_panel.select_input.comparisons.' +
          PredefinedComparison.SAME_PERIOD_JUST_BEFORE
      )
    );

    const validateFiltersButton = screen.getByTestId('validate-filters');
    act(() => {
      userEvent.click(validateFiltersButton);
    });

    expect(handleFiltersChange).toHaveBeenCalledWith({
      metric: Metric.TIME_TO_ENRICH,
      period: PredefinedPeriod.LAST_12_MONTHS,
      aggregation: Aggregation.CATEGORIES,
      comparison: PredefinedComparison.SAME_PERIOD_JUST_BEFORE,
      families: [],
      channels: [],
      locales: [],
    });
  });

  it('calls onChange handler on multi select input', async () => {
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
      />
    );

    const buttons = await screen.findAllByTitle('pim_common.open');

    buttons.forEach(button => {
      userEvent.click(button);
    });

    userEvent.click(await screen.findByText('[ecommerce]'));
    userEvent.click(await screen.findByText('[family_10]'));
    userEvent.click(await screen.findByText('French'));

    const validateFiltersButton = screen.getByTestId('validate-filters');
    act(() => {
      userEvent.click(validateFiltersButton);
    });

    expect(handleFiltersChange).toHaveBeenCalledWith({
      metric: Metric.TIME_TO_ENRICH,
      period: PredefinedPeriod.LAST_12_WEEKS,
      aggregation: Aggregation.FAMILIES,
      comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
      families: ['family_10'],
      channels: ['ecommerce'],
      locales: ['fr_FR'],
    });
  });
});
