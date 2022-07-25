import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {SupplierFilesList} from './SupplierFilesList';
import {SupplierFileRow} from '../hooks';

const supplierfilesList: SupplierFileRow[] = [
    {
        supplier: 'mega supplier',
        identifier: 'file1',
        status: 'Downloaded',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
    },
    {
        supplier: 'mega supplier',
        identifier: 'file2',
        status: 'To download',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
    },
    {
        supplier: 'mega supplier',
        identifier: 'file3',
        status: 'Downloaded',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
    },
    {
        supplier: 'mega supplier',
        identifier: 'file4',
        status: 'To download',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
    },
    {
        supplier: 'mega supplier',
        identifier: 'file5',
        status: 'Downloaded',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
    },
];

test('it displays an empty placeholder when there is no files', () => {
    renderWithProviders(
        <SupplierFilesList supplierFiles={[]} totalSupplierFiles={0} currentPage={1} onChangePage={() => {}} />
    );
    expect(screen.getByText('supplier_portal.product_file_dropping.supplier_files.no_files')).toBeInTheDocument();
});

test('it renders a list of supplier files', () => {
    renderWithProviders(
        <SupplierFilesList
            supplierFiles={supplierfilesList}
            totalSupplierFiles={5}
            currentPage={1}
            onChangePage={() => {}}
        />
    );
    expect(screen.queryAllByText('mega supplier').length).toBe(5);
    expect(screen.queryAllByText('supplier_portal.product_file_dropping.supplier_files.status.downloaded').length).toBe(
        3
    );
    expect(
        screen.queryAllByText('supplier_portal.product_file_dropping.supplier_files.status.to_download').length
    ).toBe(2);
    expect(screen.queryAllByText('07/25/2022, 08:00 AM').length).toBe(5);
});
