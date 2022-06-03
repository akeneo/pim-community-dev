jest.unmock('./Edit');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Edit id={'123e4567-e89b-12d3-a456-426614174000'} />
        </ThemeProvider>
    );

    expect(screen.getByText('product selection')).toBeInTheDocument();
});
