jest.unmock('./CatalogEdit');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CatalogEdit} from './CatalogEdit';

jest.mock('../ErrorBoundary', () => ({
    ErrorBoundary: ({children}: {children: any}) => <>{children}</>,
}));

test('it renders without error', () => {
    const form = {
        values: {
            enabled: true,
            product_selection_criteria: {},
        },
        dispatch: jest.fn(),
        errors: [],
    };
    render(
        <ThemeProvider theme={pimTheme}>
            <CatalogEdit form={form} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Edit]')).toBeInTheDocument();
});
