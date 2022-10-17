import React from 'react';
import {renderWithProviders} from '../../tests';
import {screen} from '@testing-library/react';
import {TimeToEnrichDashboard} from './TimeToEnrichDashboard';

describe('TimeToEnrichDashboard', () => {
  it('renders the TTE dashboard page', async () => {
    await renderWithProviders(<TimeToEnrichDashboard />);

    expect(await screen.findByText('2021-W45')).toBeInTheDocument();
    expect(await screen.findByText('10')).toBeInTheDocument();
  });
});
