jest.unmock('./CatalogEdit');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CatalogEdit, CatalogEditRef} from './CatalogEdit';

jest.mock('../ErrorBoundary', () => ({
    ErrorBoundary: ({children}: {children: any}) => <>{children}</>,
}));

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <CatalogEdit id={'123e4567-e89b-12d3-a456-426614174000'} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Edit]')).toBeInTheDocument();
});
