import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {mockFetchResponses} from '../../../../../tests/mockFetchResponses';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import {Operator} from '../../models/Operator';
import {AttributeNumberCriterion} from './AttributeNumberCriterion';

jest.mock('../../hooks/useOperatorTranslator');

const ECOMMERCE = {code: 'ecommerce', label: 'E-commerce'};
const PRINT = {code: 'print', label: 'Print'};

const EN = {code: 'en_US', label: 'English'};
const FR = {code: 'fr_FR', label: 'French'};
const DE = {code: 'de_DE', label: 'German'};

const openDropdown = (selector: string): void => {
    const container = screen.getByTestId(selector);
    const input = within(container).getByRole('textbox');
    fireEvent.click(input);
    fireEvent.focus(input);
};

beforeEach(() => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/locales',
            json: [EN, FR, DE],
        },
        {
            url: '/rest/catalogs/channels?page=1&limit=20',
            json: [ECOMMERCE, PRINT],
        },
        {
            url: '/rest/catalogs/channels/ecommerce',
            json: ECOMMERCE,
        },
        {
            url: '/rest/catalogs/channels/ecommerce/locales',
            json: [EN, FR, DE],
        },
        {
            url: '/rest/catalogs/attributes/number_battery_cells_localizable_scopable',
            json: {
                code: 'number_battery_cells_localizable_scopable',
                label: 'Number of battery cells',
                type: 'pim_catalog_number',
                scopable: true,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/number_battery_cells_scopable',
            json: {
                code: 'number_battery_cells_scopable',
                label: 'Number of battery cells',
                type: 'pim_catalog_number',
                scopable: true,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/number_battery_cells_localizable',
            json: {
                code: 'number_battery_cells_localizable',
                label: 'Number of battery cells',
                type: 'pim_catalog_number',
                scopable: false,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/number_battery_cells',
            json: {
                code: 'number_battery_cells',
                label: 'Number of battery cells',
                type: 'pim_catalog_number',
                scopable: false,
                localizable: false,
            },
        },
    ]);
});

test('it renders the fields for a scopable and localizable attribute', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeNumberCriterion
                    state={{
                        field: 'number_battery_cells_localizable_scopable',
                        operator: Operator.EQUALS,
                        value: 4,
                        locale: 'en_US',
                        scope: 'ecommerce',
                    }}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('Number of battery cells')).toBeInTheDocument();
    expect(await screen.findByText(Operator.EQUALS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue(4)).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders the fields for a scopable and non localizable attribute', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeNumberCriterion
                    state={{
                        field: 'number_battery_cells_scopable',
                        operator: Operator.EQUALS,
                        value: 4,
                        locale: null,
                        scope: 'ecommerce',
                    }}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('Number of battery cells')).toBeInTheDocument();
    expect(await screen.findByText(Operator.EQUALS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue(4)).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(screen.queryByTestId('locale')).not.toBeInTheDocument();
});

test('it renders the non scopable and localizable number attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeNumberCriterion
                    state={{
                        field: 'number_battery_cells_localizable',
                        operator: Operator.EQUALS,
                        value: 4,
                        locale: 'en_US',
                        scope: null,
                    }}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('Number of battery cells')).toBeInTheDocument();
    expect(await screen.findByText(Operator.EQUALS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue(4)).toBeInTheDocument();
    expect(screen.queryByTestId('channel')).not.toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders criterion with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeNumberCriterion
                    state={{
                        field: 'number_battery_cells_localizable_scopable',
                        operator: Operator.EQUALS,
                        value: 4,
                        locale: 'en_US',
                        scope: 'ecommerce',
                    }}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{
                        field: undefined,
                        operator: 'Invalid operator.',
                        value: 'Invalid value.',
                        scope: 'Invalid scope.',
                        locale: 'Invalid locale.',
                    }}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(screen.getByText('Invalid operator.')).toBeInTheDocument();
    expect(screen.getByText('Invalid value.')).toBeInTheDocument();
    expect(screen.getByText('Invalid scope.')).toBeInTheDocument();
    expect(screen.getByText('Invalid locale.')).toBeInTheDocument();
});

test('it calls onRemove', () => {
    const onRemove = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeNumberCriterion
                    state={{
                        field: 'number_battery_cells',
                        operator: Operator.EQUALS,
                        value: 4,
                        locale: null,
                        scope: null,
                    }}
                    onChange={jest.fn()}
                    onRemove={onRemove}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    fireEvent.click(screen.getByTitle('akeneo_catalogs.product_selection.action.remove'));

    expect(onRemove).toHaveBeenCalled();
});

test('it calls onChange when the operator changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeNumberCriterion
                    state={{
                        field: 'number_battery_cells',
                        operator: Operator.EQUALS,
                        value: 4,
                        locale: null,
                        scope: null,
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    openDropdown('operator');
    fireEvent.click(await screen.findByText(Operator.IS_NOT_EMPTY));

    expect(onChange).toHaveBeenCalledWith({
        field: 'number_battery_cells',
        operator: Operator.IS_NOT_EMPTY,
        value: null,
        locale: null,
        scope: null,
    });
});

test('it calls onChange when the value changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeNumberCriterion
                    state={{
                        field: 'number_battery_cells',
                        operator: Operator.EQUALS,
                        value: 4,
                        locale: null,
                        scope: null,
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    fireEvent.change(await screen.findByTestId('value'), {target: {value: 2}});

    expect(onChange).toHaveBeenCalledWith({
        field: 'number_battery_cells',
        operator: Operator.EQUALS,
        value: 2,
        locale: null,
        scope: null,
    });
});

test('it calls onChange when the channel changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeNumberCriterion
                    state={{
                        field: 'number_battery_cells_scopable',
                        operator: Operator.EQUALS,
                        value: 4,
                        locale: null,
                        scope: 'ecommerce',
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('E-commerce')).toBeInTheDocument();

    openDropdown('scope');
    fireEvent.click(await screen.findByText('Print'));

    expect(onChange).toHaveBeenCalledWith({
        field: 'number_battery_cells_scopable',
        operator: Operator.EQUALS,
        value: 4,
        locale: null,
        scope: 'print',
    });
});

test('it calls onChange when the locale changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeNumberCriterion
                    state={{
                        field: 'number_battery_cells_localizable',
                        operator: Operator.EQUALS,
                        value: 4,
                        locale: 'en_US',
                        scope: null,
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('English')).toBeInTheDocument();

    openDropdown('locale');
    fireEvent.click(await screen.findByText('French'));

    expect(onChange).toHaveBeenCalledWith({
        field: 'number_battery_cells_localizable',
        operator: Operator.EQUALS,
        value: 4,
        locale: 'fr_FR',
        scope: null,
    });
});
