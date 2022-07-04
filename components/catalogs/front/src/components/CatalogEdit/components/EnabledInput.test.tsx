jest.unmock('./EnabledInput');

import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {EnabledInput} from './EnabledInput';
import {CatalogFormContext} from '../contexts/CatalogFormContext';
import {CatalogFormActions} from '../reducers/CatalogFormReducer';

test('it renders false without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <EnabledInput value={false} error={null} />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.settings.fields.enabled')).toBeInTheDocument();
    expect(screen.getByText('akeneo_catalogs.settings.inputs.no')).toHaveAttribute('value', 'false');
});

test('it renders true without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <EnabledInput value={true} error={null} />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.settings.fields.enabled')).toBeInTheDocument();
    expect(screen.getByText('akeneo_catalogs.settings.inputs.yes')).toHaveAttribute('value', 'true');
});

test('it dispatches when the value change', () => {
    const dispatch = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <CatalogFormContext.Provider value={dispatch}>
                <EnabledInput value={false} error={null} />
            </CatalogFormContext.Provider>
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('akeneo_catalogs.settings.inputs.yes'));
    expect(dispatch).toHaveBeenCalledWith({type: CatalogFormActions.SET_ENABLED, value: true});
    fireEvent.click(screen.getByText('akeneo_catalogs.settings.inputs.no'));
    expect(dispatch).toHaveBeenCalledWith({type: CatalogFormActions.SET_ENABLED, value: false});
});
