import React from 'react';
import {renderWithProviders} from '../../../tests';
import {screen} from '@testing-library/react';
import {EmptyProductFileHistory} from './EmptyProductFileHistory';

test('it displays an empty placeholder when the history is empty', () => {
    renderWithProviders(<EmptyProductFileHistory />);

    expect(screen.getByText('Your file history is empty.')).toBeInTheDocument();
    expect(screen.getByText('Please share an XLSX file first.')).toBeInTheDocument();
});
