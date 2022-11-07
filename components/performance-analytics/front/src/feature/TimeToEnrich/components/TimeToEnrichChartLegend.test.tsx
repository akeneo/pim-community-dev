import React from 'react';
import {renderWithProviders, translate} from '@akeneo-pim-community/shared';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {TimeToEnrichChartLegend} from './TimeToEnrichChartLegend';
import {defaultFilters} from '../../Common';

describe('TimeToEnrichChartLegend', () => {
  it('renders the legend with closed control panel', async () => {
    renderWithProviders(
      <TimeToEnrichChartLegend filters={defaultFilters} isControlPanelOpen={false} onControlPanelClick={() => {}} />
    );

    expect(
      screen.getByText(
        translate('akeneo.performance_analytics.control_panel.select_input.metrics.' + defaultFilters.metric)
      )
    ).toBeInTheDocument();
    expect(screen.getByText('akeneo.performance_analytics.control_panel.open_control_panel')).toBeInTheDocument();
  });

  it('renders the legend with opened control panel', async () => {
    const handleControlPanelClick = jest.fn();

    renderWithProviders(
      <TimeToEnrichChartLegend
        filters={defaultFilters}
        isControlPanelOpen={true}
        onControlPanelClick={handleControlPanelClick}
      />
    );

    const controlPanelButton = screen.getByText('akeneo.performance_analytics.control_panel.close_control_panel');
    expect(
      screen.getByText(
        translate('akeneo.performance_analytics.control_panel.select_input.metrics.' + defaultFilters.metric)
      )
    ).toBeInTheDocument();
    expect(controlPanelButton).toBeInTheDocument();

    act(() => {
      userEvent.click(controlPanelButton);
    });

    expect(handleControlPanelClick).toBeCalled();
  });
});
