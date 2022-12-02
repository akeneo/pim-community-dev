import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {TimeToEnrichTable} from './TimeToEnrichTable';

const tableData = [
  {
    code: 'accessories',
    value: 44,
  },
  {
    code: 'camcorders',
    value: 10,
  },
  {
    code: 'clothing',
    value: 41,
  },
  {
    code: 'digital_cameras',
    value: 100,
  },
];

describe('TimeToEnrichTable', () => {
  it('renders a table', async () => {
    renderWithProviders(<TimeToEnrichTable tableData={tableData} />);

    expect(await screen.findByText('akeneo.performance_analytics.table.header_families')).toBeInTheDocument();
    expect(await screen.findByText('akeneo.performance_analytics.table.header_time_to_enrich')).toBeInTheDocument();
    expect(await screen.findByText('accessories')).toBeInTheDocument();
  });
});
