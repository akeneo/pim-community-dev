import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CatalogEdit} from './CatalogEdit';
import {CatalogFormContext} from './contexts/CatalogFormContext';
import {CatalogFormActions} from './reducers/CatalogFormReducer';
import {Operator} from '../ProductSelection';
import {mocked} from 'ts-jest/utils';
import {generateRandomId} from '../ProductSelection/utils/generateRandomId';
import {QueryClient, QueryClientProvider} from 'react-query';

jest.mock('../ProductSelection/utils/generateRandomId');

test('it can enable a catalog', () => {
    const dispatch = jest.fn();
    const form = {
        values: {
            enabled: true,
            product_selection_criteria: {},
            filter_values_criteria: {channel: []},
        },
        dispatch: dispatch,
        errors: [],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <CatalogFormContext.Provider value={dispatch}>
                    <CatalogEdit form={form} />
                </CatalogFormContext.Provider>
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('akeneo_catalogs.catalog_edit.tabs.settings'));
    fireEvent.click(screen.getByText('akeneo_catalogs.settings.inputs.yes'));
    expect(dispatch).toHaveBeenCalledWith({type: CatalogFormActions.SET_ENABLED, value: true});
});

test('it can change criteria in the product selection', async () => {
    mocked(generateRandomId).mockReturnValue('rdn');

    const dispatch = jest.fn();
    const form = {
        values: {
            enabled: true,
            product_selection_criteria: {},
            filter_values_criteria: {channel: []},
        },
        dispatch: dispatch,
        errors: [],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <CatalogFormContext.Provider value={dispatch}>
                    <CatalogEdit form={form} />
                </CatalogFormContext.Provider>
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('akeneo_catalogs.catalog_edit.tabs.product_selection'));
    fireEvent.click(await screen.findByText('akeneo_catalogs.product_selection.add_criteria.label'));
    fireEvent.click(await screen.findByText('akeneo_catalogs.product_selection.criteria.status.label'));

    expect(await screen.findByText('akeneo_catalogs.product_selection.criteria.status.label')).toBeInTheDocument();
    expect(dispatch).toHaveBeenCalledWith({
        type: CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA,
        value: {
            rdn: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            },
        },
    });
});

test('it says hello world!', () => {
    const dispatch = jest.fn();
    const form = {
        values: {
            enabled: true,
            product_selection_criteria: {},
            filter_values_criteria: {channel: []},
        },
        dispatch: dispatch,
        errors: [],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <CatalogFormContext.Provider value={dispatch}>
                    <CatalogEdit form={form} />
                </CatalogFormContext.Provider>
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('akeneo_catalogs.catalog_edit.tabs.filter_values'));
    expect(screen.findByText('Hello world!')).toBeInTheDocument();
});
