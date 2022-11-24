import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '../../../tests';
import {ProductFileList} from './ProductFileList';
import {ProductFile} from '../model/ProductFile';
import {ImportStatus} from '../model/ImportStatus';

const productFiles: ProductFile[] = [
    {
        identifier: '4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86',
        filename: 'product-file-1.xlsx',
        contributor: 'contributor1@example.com',
        uploadedAt: '2022-07-28T14:57:37+00:00',
        comments: [],
        displayNewMessageIndicatorPill: false,
        supplierLastReadAt: null,
        importStatus: ImportStatus.TO_IMPORT,
    },
    {
        identifier: '8be6446b-befb-4d9f-aa94-0dfd390df690',
        filename: 'product-file-2.xlsx',
        contributor: 'contributor2@example.com',
        uploadedAt: '2022-07-28T14:58:38+00:00',
        comments: [],
        displayNewMessageIndicatorPill: false,
        supplierLastReadAt: null,
        importStatus: ImportStatus.TO_IMPORT,
    },
];

test('it renders a paginated list of product files', async () => {
    const productFileList: ProductFile[] = [...Array(25)].map((_, index) => ({
        identifier: `file${index}`,
        filename: `product-file-${index}.xlsx`,
        contributor: 'contributor@los-pollos-hermanos.com',
        uploadedAt: '2022-10-19T14:57:37+00:00',
        comments: [],
        supplierLastReadAt: null,
        displayNewMessageIndicatorPill: false,
        importStatus: ImportStatus.TO_IMPORT,
    }));

    renderWithProviders(
        <ProductFileList
            productFiles={productFileList}
            totalProductFiles={30}
            currentPage={1}
            onChangePage={() => {}}
        />
    );
    expect(screen.getByText('product-file-1.xlsx')).toBeInTheDocument();
    expect(screen.queryAllByText('contributor@los-pollos-hermanos.com').length).toBe(25);
    expect(screen.queryAllByText('10/19/2022, 02:57 PM').length).toBe(25);
    expect(screen.getAllByTestId('Download icon')).toHaveLength(25);
    expect(screen.getAllByTestId('paginationItem')).toHaveLength(3);
});

test('it refreshes the comment panel content when clicking on another product file row', () => {
    renderWithProviders(
        <ProductFileList productFiles={productFiles} totalProductFiles={2} currentPage={1} onChangePage={() => {}} />
    );
    const firstProductFileRow = screen.getByTestId('4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86');
    const secondProductFileRow = screen.getByTestId('8be6446b-befb-4d9f-aa94-0dfd390df690');

    fireEvent.click(firstProductFileRow);
    fireEvent.click(secondProductFileRow);

    expect(screen.getAllByText('product-file-1.xlsx')).toHaveLength(1);
    expect(screen.getAllByText('contributor1@example.com')).toHaveLength(1);
    expect(screen.getAllByText('07/28/2022, 02:57 PM')).toHaveLength(1);
    expect(screen.getAllByText('product-file-2.xlsx')).toHaveLength(2);
    expect(screen.getAllByText('contributor2@example.com')).toHaveLength(2);
    expect(screen.getAllByText('07/28/2022, 02:58 PM')).toHaveLength(2);
});

test('it displays the number of product files', () => {
    renderWithProviders(
        <ProductFileList productFiles={productFiles} totalProductFiles={2} currentPage={1} onChangePage={() => {}} />
    );

    expect(screen.getByText('2 results')).toBeInTheDocument();
});

test('it displays the comment panel content when clicking on a product file row', () => {
    renderWithProviders(
        <ProductFileList productFiles={productFiles} totalProductFiles={2} currentPage={1} onChangePage={() => {}} />
    );
    const firstProductFileRow = screen.getByTestId('4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86');

    fireEvent.click(firstProductFileRow);

    expect(screen.getByTestId('close-panel-icon')).toBeInTheDocument();
});

test('it hides the comment panel content when clicking twice on a product file row', () => {
    renderWithProviders(
        <ProductFileList productFiles={productFiles} totalProductFiles={2} currentPage={1} onChangePage={() => {}} />
    );
    const firstProductFileRow = screen.getByTestId('4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86');

    fireEvent.click(firstProductFileRow);
    fireEvent.click(firstProductFileRow);

    expect(screen.queryByTestId('close-panel-icon')).not.toBeInTheDocument();
});
