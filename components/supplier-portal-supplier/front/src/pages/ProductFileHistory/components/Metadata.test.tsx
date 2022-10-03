import {renderWithProviders} from '../../../tests';
import {ProductFilePanel} from './ProductFilePanel';
import {screen} from '@testing-library/react';
import React from 'react';

const productFile = {
    identifier: '4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86',
    filename: 'product-file.xlsx',
    path: 'test/73d1078b-840c-4135-9564-682f8cbfb982-suppliers_export.xlsx',
    contributor: 'jimmy.punchline@los-pollos-hermanos.com',
    uploadedAt: '2022-09-21 08:34:00-00:00',
    retailerComments: [],
    supplierComments: [],
};

test('it renders the general product file information', () => {
    renderWithProviders(<ProductFilePanel productFile={productFile} closePanel={() => {}} />);

    expect(screen.getByText('product-file.xlsx')).toBeInTheDocument();
    expect(screen.getByText('jimmy.punchline@los-pollos-hermanos.com')).toBeInTheDocument();
    expect(screen.getByText('09/21/2022, 08:34 AM')).toBeInTheDocument();
});
