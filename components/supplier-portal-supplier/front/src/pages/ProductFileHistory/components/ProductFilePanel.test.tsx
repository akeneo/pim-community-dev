import {renderWithProviders} from '../../../tests';
import {ProductFilePanel} from './ProductFilePanel';
import React from 'react';
import {screen} from '@testing-library/react';

test('it renders nothing if the product file because the user did not click on any product file row', () => {
    renderWithProviders(<ProductFilePanel productFile={null} closePanel={() => {}} />);

    expect(screen.queryByText('product-file.xlsx')).not.toBeInTheDocument();
    expect(screen.queryByText('jimmy.punchline@los-pollos-hermanos.com')).not.toBeInTheDocument();
    expect(screen.queryByText('09/21/2022, 08:34 AM')).not.toBeInTheDocument();
});
