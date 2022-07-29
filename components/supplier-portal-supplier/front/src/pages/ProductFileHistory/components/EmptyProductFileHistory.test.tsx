import React from 'react';
import {renderWithProviders} from '../../../tests';
import {screen} from '@testing-library/react';
import {EmptyProductFileHistory} from './EmptyProductFileHistory';

test('it displays an empty placeholder when there is no product files', () => {
    renderWithProviders(<EmptyProductFileHistory />);

    expect(screen.getByText('There is no product files yet.')).toBeInTheDocument();
});
