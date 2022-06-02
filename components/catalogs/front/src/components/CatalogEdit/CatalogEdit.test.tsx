jest.unmock('./CatalogEdit');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CatalogEdit} from './CatalogEdit';

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <CatalogEdit />
        </ThemeProvider>
    );

    expect(screen.getByText('CatalogEdit')).toBeInTheDocument();
});
