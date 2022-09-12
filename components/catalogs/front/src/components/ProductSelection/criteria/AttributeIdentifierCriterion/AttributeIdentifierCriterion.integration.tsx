import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import {Operator} from '../../models/Operator';
import {AttributeIdentifierCriterion} from './AttributeIdentifierCriterion';
import {mockFetchResponses} from '../../../../../tests/mockFetchResponses';

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
            url: '/rest/catalogs/attributes/sku_localizable_scopable',
            json: {
                code: 'sku_localizable_scopable',
                label: 'SKU',
                type: 'pim_catalog_identifier',
                scopable: true,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/sku_scopable',
            json: {
                code: 'sku_scopable',
                label: 'SKU',
                type: 'pim_catalog_identifier',
                scopable: true,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/sku_localizable',
            json: {
                code: 'sku_localizable',
                label: 'SKU',
                type: 'pim_catalog_identifier',
                scopable: false,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/sku',
            json: {
                code: 'sku',
                label: 'SKU',
                type: 'pim_catalog_identifier',
                scopable: false,
                localizable: false,
            },
        },
    ]);
});

test('it renders the scopable and localizable criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku_localizable_scopable',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
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

    expect(await screen.findByText('SKU')).toBeInTheDocument();
    expect(await screen.findByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(await screen.findByText('blue-tshirt')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders the scopable and non localizable criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku_scopable',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
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

    expect(await screen.findByText('SKU')).toBeInTheDocument();
    expect(await screen.findByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(await screen.findByText('blue-tshirt')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(screen.queryByTestId('locale')).not.toBeInTheDocument();
});

test('it renders the non scopable and localizable criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku_localizable',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
                        locale: 'de_DE',
                        scope: null,
                    }}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('SKU')).toBeInTheDocument();
    expect(await screen.findByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(await screen.findByText('blue-tshirt')).toBeInTheDocument();
    expect(screen.queryByTestId('scope')).not.toBeInTheDocument();
    expect(await screen.findByText('German')).toBeInTheDocument();
});

test('it renders criterion with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku_localizable_scopable',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
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
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
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
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
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
    fireEvent.click(await screen.findByText(Operator.NOT_IN_LIST));

    expect(onChange).toHaveBeenCalledWith({
        field: 'sku',
        operator: Operator.NOT_IN_LIST,
        value: ['blue-tshirt'],
        locale: null,
        scope: null,
    });
});

test('it resets the value when the operator changes from multi to single value', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
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
    fireEvent.click(await screen.findByText(Operator.EQUALS));

    expect(onChange).toHaveBeenCalledWith({
        field: 'sku',
        operator: Operator.EQUALS,
        value: '',
        locale: null,
        scope: null,
    });
});

test('it resets the value when the operator changes from single to multi value', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku',
                        operator: Operator.EQUALS,
                        value: 'blue-tshirt',
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
    fireEvent.click(await screen.findByText(Operator.IN_LIST));

    expect(onChange).toHaveBeenCalledWith({
        field: 'sku',
        operator: Operator.IN_LIST,
        value: [],
        locale: null,
        scope: null,
    });
});

test('it calls onChange when the single value changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku',
                        operator: Operator.EQUALS,
                        value: 'blue-tshirt',
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

    const input = screen.getByTestId('value');
    fireEvent.change(input, {target: {value: 'red-tshirt'}});

    expect(onChange).toHaveBeenCalledWith({
        field: 'sku',
        operator: Operator.EQUALS,
        value: 'red-tshirt',
        locale: null,
        scope: null,
    });
});

test('it calls onChange when the multi value changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
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

    const input = screen.getByTestId('value');
    userEvent.type(input, 'red-tshirt{enter}');

    expect(onChange).toHaveBeenCalledWith({
        field: 'sku',
        operator: Operator.IN_LIST,
        value: ['blue-tshirt', 'red-tshirt'],
        locale: null,
        scope: null,
    });
});

test('it calls onChange when the channel changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku_scopable',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
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
        field: 'sku_scopable',
        operator: Operator.IN_LIST,
        value: ['blue-tshirt'],
        locale: null,
        scope: 'print',
    });
});

test('it calls onChange when the locale changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeIdentifierCriterion
                    state={{
                        field: 'sku_localizable',
                        operator: Operator.IN_LIST,
                        value: ['blue-tshirt'],
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
        field: 'sku_localizable',
        operator: Operator.IN_LIST,
        value: ['blue-tshirt'],
        locale: 'fr_FR',
        scope: null,
    });
});
