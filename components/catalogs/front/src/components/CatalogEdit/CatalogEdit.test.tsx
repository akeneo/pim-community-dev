import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CatalogEdit} from './CatalogEdit';

jest.unmock('./CatalogEdit');

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <CatalogEdit />
        </ThemeProvider>
    );

    expect(screen.getByText('CatalogList')).toBeInTheDocument();
});
