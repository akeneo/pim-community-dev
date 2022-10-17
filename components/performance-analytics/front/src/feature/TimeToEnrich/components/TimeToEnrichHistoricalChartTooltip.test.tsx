import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {TimeToEnrichHistoricalChartTooltip} from './TimeToEnrichHistoricalChartTooltip';

describe('TimeToEnrichHistoricalChartTooltip', () => {
  it('renders a tooltip', async () => {
    renderWithProviders(
      <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <TimeToEnrichHistoricalChartTooltip x={10} y={20} />
      </svg>
    );

    expect(screen.getByText('TODO, need to be implemented.')).toBeInTheDocument();
  });
});
