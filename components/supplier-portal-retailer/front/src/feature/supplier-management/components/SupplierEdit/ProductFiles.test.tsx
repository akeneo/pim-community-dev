import {renderWithProviders} from '@akeneo-pim-community/shared';
import {act, screen} from '@testing-library/react';
import React from 'react';
import {ProductFiles} from './ProductFiles';

const backendResponse = {
    product_files: [],
    total: 0,
    items_per_page: 25,
};

test('it renders an empty list', async () => {
    // @ts-ignore
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => backendResponse,
    }));

    await act(async () => {
        renderWithProviders(<ProductFiles supplierIdentifier={'d8d5824b-afdb-41a9-93a4-6a76a8b15c08'} />);
    });

    expect(screen.getByText('supplier_portal.product_file_dropping.supplier_files.no_files')).toBeInTheDocument();
});
