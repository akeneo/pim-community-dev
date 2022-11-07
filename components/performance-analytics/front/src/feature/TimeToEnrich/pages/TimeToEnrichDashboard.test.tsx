import React from 'react';
import {renderWithProviders} from '../../tests';
import {act, fireEvent, screen} from '@testing-library/react';
import {TimeToEnrichDashboard} from './TimeToEnrichDashboard';
import userEvent from '@testing-library/user-event';
import {PredefinedPeriod} from '../../Common';

describe('TimeToEnrichDashboard', () => {
  it('renders the TTE dashboard page', async () => {
    await renderWithProviders(<TimeToEnrichDashboard />);

    expect(await screen.findByText('2021-W45')).toBeInTheDocument();
    expect(await screen.findByText('10')).toBeInTheDocument();

    act(() => {
      userEvent.click(screen.getByText('akeneo.performance_analytics.control_panel.open_control_panel'));
    });

    expect(screen.getByText('akeneo.performance_analytics.control_panel.close_control_panel')).toBeInTheDocument();
  });

  it('validates the control panel filters', async () => {
    await renderWithProviders(<TimeToEnrichDashboard />);

    userEvent.click(screen.getByText('akeneo.performance_analytics.control_panel.open_control_panel'));

    const validateFiltersButton = screen.getByTestId('validate-filters');
    act(() => {
      userEvent.click(validateFiltersButton);
    });

    expect(
      await screen.findByText('akeneo.performance_analytics.control_panel.open_control_panel')
    ).toBeInTheDocument();
  });

  it('changes the filter period value', async () => {
    await renderWithProviders(<TimeToEnrichDashboard />);

    userEvent.click(screen.getByText('akeneo.performance_analytics.control_panel.open_control_panel'));

    const [metricInput, aggregationInput, periodInput] = await screen.findAllByRole('textbox');
    expect(metricInput).toBeInTheDocument();
    expect(aggregationInput).toBeInTheDocument();

    fireEvent.click(periodInput);
    userEvent.click(
      screen.getByText(
        'akeneo.performance_analytics.control_panel.select_input.periods.' + PredefinedPeriod.LAST_12_MONTHS
      )
    );

    const validateFiltersButton = screen.getByTestId('validate-filters');
    act(() => {
      userEvent.click(validateFiltersButton);
    });

    expect(
      await screen.findByText('akeneo.performance_analytics.control_panel.open_control_panel')
    ).toBeInTheDocument();
  });
});
