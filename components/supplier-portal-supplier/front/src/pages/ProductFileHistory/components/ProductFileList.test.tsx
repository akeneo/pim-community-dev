import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../tests';
import {ProductFileList} from './ProductFileList';

test('it displays an empty placeholder when there is no product files', () => {
    renderWithProviders(<ProductFileList productFiles={[]} />);

    expect(screen.getByText('There is no product files yet.')).toBeInTheDocument();
});
