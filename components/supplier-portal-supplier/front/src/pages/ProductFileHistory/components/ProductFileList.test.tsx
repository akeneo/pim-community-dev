import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '../../../tests';
import {ProductFileList} from './ProductFileList';

const productFiles = [
    {
        identifier: '4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86',
        filename: 'product-file-1.xlsx',
        path: 'test/73d1078b-840c-4135-9564-682f8cbfb982-product-file.xlsx',
        contributor: 'contributor1@example.com',
        uploadedAt: '2022-07-28 14:57:37-00:00',
    },
    {
        identifier: '8be6446b-befb-4d9f-aa94-0dfd390df690',
        filename: 'product-file-2.xlsx',
        path: 'test/73d1078b-840c-4135-9564-682f8cbfb982-product-file.xlsx',
        contributor: 'contributor2@example.com',
        uploadedAt: '2022-07-28 14:58:38-00:00',
    },
];

test('it renders the product files', () => {
    renderWithProviders(<ProductFileList productFiles={productFiles} />);

    expect(screen.getByText('product-file-1.xlsx')).toBeInTheDocument();
    expect(screen.getByText('contributor1@example.com')).toBeInTheDocument();
    expect(screen.getByText('07/28/2022, 02:57 PM')).toBeInTheDocument();
    expect(screen.getByText('product-file-2.xlsx')).toBeInTheDocument();
    expect(screen.getByText('contributor2@example.com')).toBeInTheDocument();
    expect(screen.getByText('07/28/2022, 02:58 PM')).toBeInTheDocument();
    expect(screen.getAllByTestId('Download icon')).toHaveLength(2);
});

test('it refreshes the comment panel content when clicking on another product file row', () => {
    renderWithProviders(<ProductFileList productFiles={productFiles} />);
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

test('it displays the comment panel content when clicking on a product file row', () => {
    renderWithProviders(<ProductFileList productFiles={productFiles} />);
    const firstProductFileRow = screen.getByTestId('4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86');

    fireEvent.click(firstProductFileRow);

    expect(screen.getByTestId('close-panel-icon')).toBeInTheDocument();
});

test('it hides the comment panel content when clicking twice on a product file row', () => {
    renderWithProviders(<ProductFileList productFiles={productFiles} />);
    const firstProductFileRow = screen.getByTestId('4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86');

    fireEvent.click(firstProductFileRow);
    fireEvent.click(firstProductFileRow);

    expect(screen.queryByTestId('close-panel-icon')).not.toBeInTheDocument();
});

test('it displays the number of product files', () => {
    renderWithProviders(<ProductFileList productFiles={productFiles} />);

    expect(screen.getByText('2 results')).toBeInTheDocument();
});
