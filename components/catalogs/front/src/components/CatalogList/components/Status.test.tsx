jest.unmock('./Status');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {pimTheme} from 'akeneo-design-system';
import {Status} from './Status';
import {ThemeProvider} from 'styled-components';

test('it renders an enabled badge', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Status enabled={true} />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.catalog_list.enabled')).toBeInTheDocument();
});

test('it renders an disabled badge', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Status enabled={false} />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.catalog_list.disabled')).toBeInTheDocument();
});
