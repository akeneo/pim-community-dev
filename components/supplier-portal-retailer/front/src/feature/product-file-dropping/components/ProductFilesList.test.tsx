import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ProductFilesList} from './ProductFilesList';
import {ProductFileRow} from '../hooks';
import userEvent from '@testing-library/user-event';
import {ImportStatus} from '../models/ProductFileRow';

const productFilesList: ProductFileRow[] = [
    {
        supplier: 'mega supplier',
        identifier: 'file1',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: true,
        importStatus: ImportStatus.TO_IMPORT,
    },
    {
        supplier: 'mega supplier',
        identifier: 'file2',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: false,
        importStatus: ImportStatus.IN_PROGRESS,
    },
    {
        supplier: 'mega supplier',
        identifier: 'file3',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: false,
        importStatus: ImportStatus.COMPLETED,
    },
    {
        supplier: 'mega supplier',
        identifier: 'file4',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: false,
        importStatus: ImportStatus.COMPLETED,
    },
    {
        supplier: 'mega supplier',
        identifier: 'file5',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: false,
        importStatus: ImportStatus.TO_IMPORT,
    },
];

test('it renders a list of product files', () => {
    renderWithProviders(
        <ProductFilesList
            productFiles={productFilesList}
            totalProductFiles={5}
            currentPage={1}
            onChangePage={() => {}}
        />
    );
    expect(screen.queryAllByText('mega supplier').length).toBe(5);
    expect(screen.queryAllByText('07/25/2022, 08:00 AM').length).toBe(5);
    expect(
        screen.queryAllByText('supplier_portal.product_file_dropping.supplier_files.import.status.to_import').length
    ).toBe(2);
    expect(
        screen.queryAllByText('supplier_portal.product_file_dropping.supplier_files.import.status.completed').length
    ).toBe(2);
    expect(
        screen.queryAllByText('supplier_portal.product_file_dropping.supplier_files.import.status.in_progress').length
    ).toBe(1);
});

test('it renders a paginated list of product files', async () => {
    const productFilesList: ProductFileRow[] = [...Array(25)].map((_, index) => ({
        supplier: 'mega supplier',
        identifier: `file${index}`,
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
    }));

    const changePageCallback = jest.fn();
    renderWithProviders(
        <ProductFilesList
            productFiles={productFilesList}
            totalProductFiles={30}
            currentPage={1}
            onChangePage={changePageCallback}
        />
    );
    expect(screen.queryAllByText('mega supplier').length).toBe(25);
    expect(screen.getAllByTestId('paginationItem')).toHaveLength(2);

    await act(async () => {
        userEvent.click(screen.queryAllByTestId('paginationItem')[1]);
    });

    expect(changePageCallback).toHaveBeenNthCalledWith(1, 2);
});

test('it displays an empty placeholder when there is no files', () => {
    renderWithProviders(
        <ProductFilesList productFiles={[]} totalProductFiles={0} currentPage={1} onChangePage={() => {}} />
    );
    expect(screen.getByText('supplier_portal.product_file_dropping.supplier_files.no_files')).toBeInTheDocument();
});

test('it renders a list of product files with supplier column', () => {
    renderWithProviders(
        <ProductFilesList
            productFiles={productFilesList}
            totalProductFiles={1}
            currentPage={1}
            onChangePage={() => {}}
        />
    );
    expect(
        screen.getByText('supplier_portal.product_file_dropping.supplier_files.columns.supplier')
    ).toBeInTheDocument();
});

test('it renders a list of product files without supplier column', () => {
    renderWithProviders(
        <ProductFilesList
            productFiles={productFilesList}
            totalProductFiles={1}
            currentPage={1}
            onChangePage={() => {}}
            displaySupplierColumn={false}
        />
    );
    expect(
        screen.queryByText('supplier_portal.product_file_dropping.supplier_files.columns.supplier')
    ).not.toBeInTheDocument();
});

test('it renders a list of product files with pills if there is unread comments from supplier', () => {
    renderWithProviders(
        <ProductFilesList
            productFiles={productFilesList}
            totalProductFiles={5}
            currentPage={1}
            onChangePage={() => {}}
        />
    );
    expect(screen.queryByTestId('unread-comments-pill')).toBeInTheDocument();
});
