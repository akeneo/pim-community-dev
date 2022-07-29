import React from 'react';
import {screen, waitFor} from '@testing-library/react';
import {renderWithProviders} from '../../tests';
import {ProductFileHistory} from './ProductFileHistory';
import * as fetcher from './api/fetchProductFiles';

const productFilesResponseContent = [
    {
        identifier: '4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86',
        filename: 'suppliers_export.xlsx',
        path: 'test/73d1078b-840c-4135-9564-682f8cbfb982-suppliers_export.xlsx',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-28 14:57:38',
    },
    {
        identifier: '8be6446b-befb-4d9f-aa94-0dfd390df690',
        filename: 'suppliers_export-2.xlsx',
        path: 'test/73d1078b-840c-4135-9564-682f8cbfb982-suppliers_export.xlsx',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-28 14:57:38',
    },
];

beforeEach(() => {
    // @ts-ignore
    fetcher.fetchProductFiles = jest.fn().mockReturnValue(productFilesResponseContent);
});

test('it displays an empty placeholder when there is no product files', async () => {
    // @ts-ignore
    fetcher.fetchProductFiles = jest.fn().mockReturnValue([]);

    renderWithProviders(<ProductFileHistory />);

    await waitFor(() => {
        expect(screen.getByText('There is no product files yet.')).toBeInTheDocument();
    });
});

test('it displays a list of product files', async () => {
    renderWithProviders(<ProductFileHistory />);

    await waitFor(() => {
        expect(screen.getByText('suppliers_export.xlsx')).toBeInTheDocument();
    });
});
