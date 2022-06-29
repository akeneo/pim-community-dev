jest.unmock('./Edit');
jest.unmock('./TabBar');

import React from 'react';
import {act, render, screen} from '@testing-library/react';
import {mocked} from 'ts-jest/utils';
import userEvent from '@testing-library/user-event';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Edit} from './Edit';
import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {CatalogFormValues} from '../models/CatalogFormValues';
import {getTabsValidationStatus} from '../utils/getTabsValidationStatus';
import {Tabs} from './TabBar';

jest.mock('../../ProductSelection', () => ({
    ProductSelection: () => <>[ProductSelection]</>,
}));
jest.mock('./Settings', () => ({
    Settings: () => <>[Settings]</>,
}));

mocked(getTabsValidationStatus).mockImplementation(() => ({
    [Tabs.SETTINGS]: false,
    [Tabs.PRODUCT_SELECTION]: false,
}));

test('it renders without error', () => {
    const values: CatalogFormValues = {
        enabled: true,
        product_selection_criteria: {},
    };
    const errors: CatalogFormErrors = [];

    render(
        <ThemeProvider theme={pimTheme}>
            <Edit values={values} errors={errors} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Settings]')).toBeInTheDocument();
});

test('it switches between tabs', () => {
    const values: CatalogFormValues = {
        enabled: true,
        product_selection_criteria: {},
    };
    const errors: CatalogFormErrors = [];

    render(
        <ThemeProvider theme={pimTheme}>
            <Edit values={values} errors={errors} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Settings]')).toBeInTheDocument();

    act(() => userEvent.click(screen.getByText('akeneo_catalogs.catalog_edit.tabs.product_selection')));

    expect(screen.getByText('[ProductSelection]')).toBeInTheDocument();

    act(() => userEvent.click(screen.getByText('akeneo_catalogs.catalog_edit.tabs.settings')));

    expect(screen.getByText('[Settings]')).toBeInTheDocument();
});
