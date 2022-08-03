import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../tests';
import {ProductFileList} from './ProductFileList';

const productFiles = [
    {
        identifier: '4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86',
        filename: 'suppliers_export-1.xlsx',
        path: 'test/73d1078b-840c-4135-9564-682f8cbfb982-suppliers_export.xlsx',
        contributor: 'contributor1@example.com',
        uploadedAt: '2022-07-28 14:57:37',
    },
    {
        identifier: '8be6446b-befb-4d9f-aa94-0dfd390df690',
        filename: 'suppliers_export-2.xlsx',
        path: 'test/73d1078b-840c-4135-9564-682f8cbfb982-suppliers_export.xlsx',
        contributor: 'contributor2@example.com',
        uploadedAt: '2022-07-28 14:58:38',
    },
];

test('it renders the product files', () => {
    renderWithProviders(<ProductFileList productFiles={productFiles} />);

    expect(screen.getByText('suppliers_export-1.xlsx')).toBeInTheDocument();
    expect(screen.getByText('contributor1@example.com')).toBeInTheDocument();
    expect(screen.getByText('07/28/2022, 02:57 PM')).toBeInTheDocument();
    expect(screen.getByText('suppliers_export-2.xlsx')).toBeInTheDocument();
    expect(screen.getByText('contributor2@example.com')).toBeInTheDocument();
    expect(screen.getByText('07/28/2022, 02:57 PM')).toBeInTheDocument();
});
