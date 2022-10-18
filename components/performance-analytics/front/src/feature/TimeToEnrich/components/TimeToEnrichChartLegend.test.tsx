import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {TimeToEnrichChartLegend} from './TimeToEnrichChartLegend';

describe('TimeToEnrichChartLegend', () => {
  it('renders the legend', async () => {
    renderWithProviders(<TimeToEnrichChartLegend filters={{family: 'Accessories'}} />);

    expect(screen.getByText('Time-to-enrich')).toBeInTheDocument();
    expect(screen.getByText('akeneo.performance_analytics.graph.control_panel_button')).toBeInTheDocument();
  });
});
