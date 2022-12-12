import React from 'react';
import {renderWithProviders} from '../../tests';
import {screen} from '@testing-library/react';
import {TimeToEnrichTable} from './TimeToEnrichTable';
import {userContext} from '@akeneo-pim-community/shared';

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
  {
    code: 'unknown',
    value: 100,
  },
];

describe('TimeToEnrichTable', () => {
  it('renders a table', async () => {
    userContext.set('catalogLocale', 'en_US', {});
    await renderWithProviders(<TimeToEnrichTable tableData={tableData} />);

    expect(await screen.findByText('akeneo.performance_analytics.table.header_family')).toBeInTheDocument();
    expect(await screen.findByText('akeneo.performance_analytics.table.header_time_to_enrich')).toBeInTheDocument();
    expect(await screen.findByText('Accessories')).toBeInTheDocument();
    expect(await screen.findByText('Digital cameras')).toBeInTheDocument();
    expect(await screen.findByText('[unknown]')).toBeInTheDocument();
  });
});
