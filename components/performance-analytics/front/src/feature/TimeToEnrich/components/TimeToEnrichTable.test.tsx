import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {TimeToEnrichTable} from './TimeToEnrichTable';

describe('TimeToEnrichTable', () => {
  it('renders a table', async () => {
    renderWithProviders(<TimeToEnrichTable />);

    expect(screen.getByText('Families')).toBeInTheDocument();
    expect(screen.getByText('Time-to-enrich (in days)')).toBeInTheDocument();
    expect(screen.getByText('Same period last year')).toBeInTheDocument();
    expect(screen.getByText('Global')).toBeInTheDocument();
    expect(screen.getByText('23')).toBeInTheDocument();
    expect(screen.getByText('21')).toBeInTheDocument();

    userEvent.paste(screen.getByTitle('Search'), 'hey!');
  });
});
