import React from 'react';
import {renderWithProviders, translate} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {TimeToEnrichChartLegend} from './TimeToEnrichChartLegend';
import {defaultFilters} from '../../Common';

describe('TimeToEnrichChartLegend', () => {
  it('renders the legend', async () => {
    renderWithProviders(<TimeToEnrichChartLegend filters={defaultFilters} />);

    expect(
      screen.getByText(
        translate('akeneo.performance_analytics.control_panel.select_input.metrics.' + defaultFilters.metric)
      )
    ).toBeInTheDocument();
  });
});
