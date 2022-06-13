import userEvent from '@testing-library/user-event';

jest.unmock('./Settings');

import React from 'react';
import {act, render, screen} from '@testing-library/react';
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

test('it enables the catalog', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Settings />
        </ThemeProvider>
    );

    act(() => {
        userEvent.click(screen.getByText('akeneo_catalogs.settings.inputs.yes'));
    });

    expect(screen.getByText('akeneo_catalogs.settings.inputs.yes')).toHaveAttribute('value', 'true');

    act(() => {
        userEvent.click(screen.getByText('akeneo_catalogs.settings.inputs.no'));
    });

    expect(screen.getByText('akeneo_catalogs.settings.inputs.yes')).toHaveAttribute('value', 'false');
});
