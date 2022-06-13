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

test('it calls save from parent component', () => {
    const logger = jest.spyOn(console, 'log');
    logger.mockImplementation(() => {});

    const ref: {current: CatalogEditRef | null} = {
        current: null,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <CatalogEdit id={'123e4567-e89b-12d3-a456-426614174000'} ref={ref} />
        </ThemeProvider>
    );

    expect(ref.current).not.toBeUndefined();

    ref.current && ref.current.save();

    expect(logger).toHaveBeenCalledWith('Catalog 123e4567-e89b-12d3-a456-426614174000 saved.');
});
