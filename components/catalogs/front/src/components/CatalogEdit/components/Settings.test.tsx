jest.unmock('./Settings');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Settings} from './Settings';

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Settings />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.settings.fields.enabled')).toBeInTheDocument();
    expect(screen.getByText('akeneo_catalogs.settings.inputs.no')).toBeInTheDocument();
    expect(screen.getByText('akeneo_catalogs.settings.inputs.yes')).toBeInTheDocument();
});
