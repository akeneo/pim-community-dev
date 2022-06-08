jest.unmock('./CatalogList');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CatalogList} from './CatalogList';

jest.mock('../ErrorBoundary', () => ({
    ErrorBoundary: ({children}: {children: any}) => <>{children}</>,
}));

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <CatalogList owner={'owner name'} />
        </ThemeProvider>
    );

    expect(screen.getByText('[List]')).toBeInTheDocument();
});
