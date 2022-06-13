jest.unmock('./Empty');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Empty} from './Empty';

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Empty />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_selection.empty')).toBeInTheDocument();
});
