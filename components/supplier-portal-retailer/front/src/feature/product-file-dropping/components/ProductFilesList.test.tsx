import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ProductFilesList} from './ProductFilesList';
import {ProductFileRow} from '../models/ProductFileRow';
import userEvent from '@testing-library/user-event';
import {ImportStatus} from '../models/ImportStatus';

const productFilesList: ProductFileRow[] = [
    {
        supplier: 'mega supplier',
        identifier: 'file1',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: true,
        importStatus: ImportStatus.TO_IMPORT,
        filename: 'file1.xlsx',
    },
    {
        supplier: 'mega supplier',
        identifier: 'file2',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: false,
        importStatus: ImportStatus.IN_PROGRESS,
        filename: 'file2.xlsx',
    },
    {
        supplier: 'mega supplier',
        identifier: 'file3',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: false,
        importStatus: ImportStatus.COMPLETED,
        filename: 'file3.xlsx',
    },
    {
        supplier: 'mega supplier',
        identifier: 'file4',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: false,
        importStatus: ImportStatus.COMPLETED,
        filename: 'file4.xlsx',
    },
    {
        supplier: 'mega supplier',
        identifier: 'file5',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-25T08:00:00+00:00',
        hasUnreadComments: false,
        importStatus: ImportStatus.TO_IMPORT,
        filename: 'file5.xlsx',
    },
];

test('it renders a list of product files', () => {
    renderWithProviders(
        <ProductFilesList
            productFiles={productFilesList}
            totalSearchResults={5}
            currentPage={1}
            onChangePage={() => {}}
            searchValue={''}
            onSearch={jest.fn}
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
        filename: 'file1.xlsx',
        hasUnreadComments: false,
        importStatus: 'in_progress',
    }));

    const changePageCallback = jest.fn();
    renderWithProviders(
        <ProductFilesList
            productFiles={productFilesList}
            totalSearchResults={30}
            currentPage={1}
            onChangePage={changePageCallback}
            searchValue={''}
            onSearch={jest.fn}
        />
    );
    expect(screen.queryAllByText('mega supplier').length).toBe(25);
    expect(screen.getAllByTestId('paginationItem')).toHaveLength(2);

    await act(async () => {
        userEvent.click(screen.queryAllByTestId('paginationItem')[1]);
    });

    expect(changePageCallback).toHaveBeenNthCalledWith(1, 2);
});

test('it renders a list of product files with supplier column', () => {
    renderWithProviders(
        <ProductFilesList
            productFiles={productFilesList}
            totalSearchResults={1}
            currentPage={1}
            onChangePage={() => {}}
            searchValue={''}
            onSearch={jest.fn}
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
            totalSearchResults={1}
            currentPage={1}
            onChangePage={() => {}}
            displaySupplierColumn={false}
            searchValue={''}
            onSearch={jest.fn}
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
            totalSearchResults={5}
            currentPage={1}
            onChangePage={() => {}}
            searchValue={''}
            onSearch={jest.fn}
        />
    );
    expect(screen.queryByTestId('unread-comments-pill')).toBeInTheDocument();
});

test('it calls the callback when a user search for product files', () => {
    const onSearchCallback = jest.fn();
    renderWithProviders(
        <ProductFilesList
            productFiles={productFilesList}
            totalSearchResults={5}
            currentPage={1}
            onChangePage={() => {}}
            searchValue={''}
            onSearch={onSearchCallback}
        />
    );

    const searchField = screen.getByPlaceholderText(
        'supplier_portal.product_file_dropping.supplier_files.search.placeholder'
    );
    expect(searchField).toBeInTheDocument();

    userEvent.type(searchField, 'file');

    expect(onSearchCallback).toHaveBeenCalledTimes(4);
});
