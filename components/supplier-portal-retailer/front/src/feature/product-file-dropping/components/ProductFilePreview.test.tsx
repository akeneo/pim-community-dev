import React from 'react';
import {act, screen} from '@testing-library/react';
import {generateExcelColumnLetter, ProductFilePreview} from './ProductFilePreview';
import {renderWithProviders} from '@akeneo-pim-community/shared/';

test('it generates column letters from column indexes', () => {
    expect(generateExcelColumnLetter(0)).toBe('A');
    expect(generateExcelColumnLetter(25)).toBe('Z');
    expect(generateExcelColumnLetter(26)).toBe('AA');
    expect(generateExcelColumnLetter(51)).toBe('AZ');
    expect(generateExcelColumnLetter(52)).toBe('BA');
    expect(generateExcelColumnLetter(77)).toBe('BZ');
});

test('it renders the preview', async () => {
    global.fetch = jest.fn().mockImplementationOnce(async () => ({
        ok: true,
        json: async () => ({
            1: ['Product code', 'Label', 'Description'],
            2: ['product-1', 'Product 1', 'A beautiful product'],
            3: ['product-2', 'Product 2', 'Another amazing product'],
        }),
    }));

    await act(async () => {
        renderWithProviders(<ProductFilePreview productFileIdentifier={'0806a80b-344d-4d30-b3d7-3030b252b138'} />);
    });

    [
        'Product code',
        'Label',
        'Description',
        'product-1',
        'Product 1',
        'A beautiful product',
        'product-2',
        'Product 2',
        'Another amazing product',
    ].forEach(text => {
        expect(screen.getByText(text)).toBeInTheDocument();
    });
});
