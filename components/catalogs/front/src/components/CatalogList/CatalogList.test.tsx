import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CatalogList} from './CatalogList';

jest.unmock('./CatalogList');

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <CatalogList />
        </ThemeProvider>
    );

    expect(screen.getByText('CatalogList')).toBeInTheDocument();
});
