import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import {Operator} from '../../models/Operator';
import {AttributeTextareaCriterion} from './AttributeTextareaCriterion';
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
            url: '/rest/catalogs/attributes/description_localizable_scopable',
            json: {
                code: 'description_localizable_scopable',
                label: 'Description',
                type: 'pim_catalog_textarea',
                scopable: true,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/description_scopable',
            json: {
                code: 'description_scopable',
                label: 'Description',
                type: 'pim_catalog_textarea',
                scopable: true,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/description_localizable',
            json: {
                code: 'description_localizable',
                label: 'Description',
                type: 'pim_catalog_textarea',
                scopable: false,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/description',
            json: {
                code: 'description',
                label: 'Description',
                type: 'pim_catalog_textarea',
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
                <AttributeTextareaCriterion
                    state={{
                        field: 'description_localizable_scopable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
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

    expect(await screen.findByText('Description')).toBeInTheDocument();
    expect(await screen.findByText(Operator.CONTAINS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue('blue')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders the scopable and non localizable criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextareaCriterion
                    state={{
                        field: 'description_scopable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
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

    expect(await screen.findByText('Description')).toBeInTheDocument();
    expect(await screen.findByText(Operator.CONTAINS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue('blue')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(screen.queryByTestId('locale')).not.toBeInTheDocument();
});

test('it renders the non scopable and localizable criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextareaCriterion
                    state={{
                        field: 'description_localizable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
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

    expect(await screen.findByText('Description')).toBeInTheDocument();
    expect(await screen.findByText(Operator.CONTAINS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue('blue')).toBeInTheDocument();
    expect(screen.queryByTestId('scope')).not.toBeInTheDocument();
    expect(await screen.findByText('German')).toBeInTheDocument();
});

test('it renders criterion with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextareaCriterion
                    state={{
                        field: 'description_localizable_scopable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
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
                <AttributeTextareaCriterion
                    state={{
                        field: 'description_localizable_scopable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
                        locale: 'en_US',
                        scope: 'ecommerce',
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
                <AttributeTextareaCriterion
                    state={{
                        field: 'description_localizable_scopable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
                        locale: 'en_US',
                        scope: 'ecommerce',
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    openDropdown('operator');
    fireEvent.click(await screen.findByText(Operator.IS_EMPTY));

    expect(onChange).toHaveBeenCalledWith({
        field: 'description_localizable_scopable',
        operator: Operator.IS_EMPTY,
        value: '',
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it calls onChange when the value changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextareaCriterion
                    state={{
                        field: 'description_localizable_scopable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
                        locale: 'en_US',
                        scope: 'ecommerce',
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    const input = screen.getByTestId('value');
    fireEvent.change(input, {target: {value: 'red'}});

    expect(onChange).toHaveBeenCalledWith({
        field: 'description_localizable_scopable',
        operator: Operator.CONTAINS,
        value: 'red',
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it calls onChange when the channel changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextareaCriterion
                    state={{
                        field: 'description_scopable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
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
        field: 'description_scopable',
        operator: Operator.CONTAINS,
        value: 'blue',
        locale: null,
        scope: 'print',
    });
});

test('it calls onChange when the locale changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextareaCriterion
                    state={{
                        field: 'description_localizable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
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
        field: 'description_localizable',
        operator: Operator.CONTAINS,
        value: 'blue',
        locale: 'fr_FR',
        scope: null,
    });
});
