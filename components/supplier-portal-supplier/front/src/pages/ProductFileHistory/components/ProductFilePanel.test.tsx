import {renderWithProviders} from '../../../tests';
import {ProductFilePanel} from './ProductFilePanel';
import React from 'react';
import {screen} from '@testing-library/react';

const productFile: any = {
    identifier: '4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86',
    filename: 'product-file-1.xlsx',
    contributor: 'contributor1@example.com',
    uploadedAt: '2022-07-28T14:57:37+00:00',
    comments: [
        {content: 'test supplier 1', createdAt: '2022-10-05T00:20:35+00:00', authorEmail: 'julia@akeneo.com'},
        {content: 'test retailer 2', createdAt: '2022-10-09T21:20:35+00:00', authorEmail: 'julia@akeneo.com'},
        {content: 'test retailer 3', createdAt: '2022-10-11T13:20:35+00:00', authorEmail: 'julia@akeneo.com'},
    ],
    displayNewMessageIndicatorPill: false,
};

test('it renders nothing if the product file because the user did not click on any product file row', () => {
    renderWithProviders(<ProductFilePanel productFile={null} closePanel={() => {}} />);

    expect(screen.queryByText('product-file.xlsx')).not.toBeInTheDocument();
    expect(screen.queryByText('jimmy.punchline@los-pollos-hermanos.com')).not.toBeInTheDocument();
    expect(screen.queryByText('09/21/2022, 08:34 AM')).not.toBeInTheDocument();
});

test('it renders the most recent comments at the end', () => {
    renderWithProviders(<ProductFilePanel productFile={productFile} closePanel={() => {}} />);

    expect(screen.getAllByTestId('commentContent').map((element: any) => element.textContent)).toStrictEqual([
        '"test supplier 1"',
        '"test retailer 2"',
        '"test retailer 3"',
    ]);
});
