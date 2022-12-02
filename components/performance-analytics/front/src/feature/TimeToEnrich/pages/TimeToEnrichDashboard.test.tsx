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
    expect(await screen.findByText('akeneo.performance_analytics.table.header_time_to_enrich')).toBeInTheDocument();

    act(() => {
      userEvent.click(screen.getByText('akeneo.performance_analytics.control_panel.configure'));
    });

    expect(screen.getByText('akeneo.performance_analytics.control_panel.title')).toBeInTheDocument();
  });

  it('renders the dashboard with opened control panel', async () => {
    await renderWithProviders(<TimeToEnrichDashboard />);

    const openControlPanelButton = screen.getByText('akeneo.performance_analytics.control_panel.configure');
    expect(openControlPanelButton).toBeInTheDocument();

    expect(screen.queryByText('akeneo.performance_analytics.control_panel.title')).not.toBeInTheDocument();

    act(() => {
      userEvent.click(openControlPanelButton);
    });

    expect(await screen.findByText('akeneo.performance_analytics.control_panel.title')).toBeInTheDocument();
    expect(screen.queryByText('akeneo.performance_analytics.control_panel.configure')).not.toBeInTheDocument();
  });

  it('hides the control panel from the dashboard', async () => {
    await renderWithProviders(<TimeToEnrichDashboard />);

    const openControlPanelButton = screen.getByText('akeneo.performance_analytics.control_panel.configure');
    expect(openControlPanelButton).toBeInTheDocument();

    expect(screen.queryByText('akeneo.performance_analytics.control_panel.title')).not.toBeInTheDocument();

    act(() => {
      userEvent.click(openControlPanelButton);
    });

    expect(await screen.findByText('akeneo.performance_analytics.control_panel.title')).toBeInTheDocument();
    expect(screen.queryByText('akeneo.performance_analytics.control_panel.configure')).not.toBeInTheDocument();

    const closeControlPanelButton = screen.getByTestId('close-control-panel');
    act(() => {
      userEvent.click(closeControlPanelButton);
    });

    expect(screen.queryByText('akeneo.performance_analytics.control_panel.configure')).toBeInTheDocument();
  });

  it('changes the control panel filters', async () => {
    await renderWithProviders(<TimeToEnrichDashboard />);

    const openControlPanelButton = await screen.findByText('akeneo.performance_analytics.control_panel.configure');
    expect(openControlPanelButton).toBeInTheDocument();

    expect(screen.queryByText('akeneo.performance_analytics.control_panel.title')).not.toBeInTheDocument();

    act(() => {
      userEvent.click(openControlPanelButton);
    });

    const [metricInput, aggregationInput, periodInput] = await screen.findAllByRole('textbox');
    expect(metricInput).toBeInTheDocument();
    expect(aggregationInput).toBeInTheDocument();

    act(() => {
      fireEvent.click(periodInput);
    });

    await act(async () => {
      fireEvent.click(
        await screen.findByText(
          'akeneo.performance_analytics.control_panel.select_input.periods.' + PredefinedPeriod.LAST_12_MONTHS
        )
      );
    });

    expect(
      screen.getAllByText(
        'akeneo.performance_analytics.control_panel.select_input.periods.' + PredefinedPeriod.LAST_12_MONTHS
      ).length
    ).toBe(2);
  });
});
