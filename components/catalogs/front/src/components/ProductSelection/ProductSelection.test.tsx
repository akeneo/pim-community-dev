jest.unmock('./ProductSelection');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductSelection} from './ProductSelection';

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection />
        </ThemeProvider>
    );

    expect(screen.getByText('[Empty]')).toBeInTheDocument();
});
