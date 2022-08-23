import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {mockFetchResponses} from '../../../../../tests/mockFetchResponses';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import {Operator} from '../../models/Operator';
import {AttributeSimpleSelectCriterion} from './AttributeSimpleSelectCriterion';

jest.mock('../../hooks/useOperatorTranslator');

const ECOMMERCE = {code: 'ecommerce', label: 'E-commerce'};
const PRINT = {code: 'print', label: 'Print'};

const EN = {code: 'en_US', label: 'English'};
const FR = {code: 'fr_FR', label: 'French'};
const DE = {code: 'de_DE', label: 'German'};

const XS = {code: 'xs', label: 'XS'};
const S = {code: 's', label: 'S'};
const M = {code: 'm', label: 'M'};
const L = {code: 'l', label: 'L'};
const XL = {code: 'xl', label: 'XL'};

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
            url: '/rest/catalogs/attributes/clothing_size_localizable_scopable',
            json: {
                code: 'clothing_size_localizable_scopable',
                label: 'Clothing size',
                type: 'pim_catalog_simpleselect',
                scopable: true,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/clothing_size_localizable_scopable/options?locale=en_US&codes=xs%2Cs&search=&page=1&limit=20',
            json: [XS, S],
        },
        {
            url: '/rest/catalogs/attributes/clothing_size_localizable_scopable/options?locale=en_US&codes=&search=&page=1&limit=20',
            json: [XS, S, M, L, XL],
        },
        {
            url: '/rest/catalogs/attributes/clothing_size_scopable',
            json: {
                code: 'clothing_size_scopable',
                label: 'Clothing size',
                type: 'pim_catalog_simpleselect',
                scopable: true,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/clothing_size_scopable/options?locale=en_US&codes=xs%2Cs&search=&page=1&limit=20',
            json: [XS, S],
        },
        {
            url: '/rest/catalogs/attributes/clothing_size_scopable/options?locale=en_US&codes=&search=&page=1&limit=20',
            json: [XS, S, M, L, XL],
        },
        {
            url: '/rest/catalogs/attributes/clothing_size_localizable',
            json: {
                code: 'clothing_size_localizable',
                label: 'Clothing size',
                type: 'pim_catalog_simpleselect',
                scopable: false,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/clothing_size_localizable/options?locale=en_US&codes=xs%2Cs&search=&page=1&limit=20',
            json: [XS, S],
        },
        {
            url: '/rest/catalogs/attributes/clothing_size_localizable/options?locale=en_US&codes=&search=&page=1&limit=20',
            json: [XS, S, M, L, XL],
        },
        {
            url: '/rest/catalogs/attributes/clothing_size',
            json: {
                code: 'clothing_size',
                label: 'Clothing size',
                type: 'pim_catalog_simpleselect',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=xs%2Cs&search=&page=1&limit=20',
            json: [XS, S],
        },
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=&search=&page=1&limit=20',
            json: [XS, S, M, L, XL],
        },
    ]);
});

test('it renders the scopable and localizable simple select attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeSimpleSelectCriterion
                    state={{
                        field: 'clothing_size_localizable_scopable',
                        operator: Operator.IN_LIST,
                        value: ['xs', 's'],
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

    expect(await screen.findByText('Clothing size')).toBeInTheDocument();
    expect(await screen.findByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(await screen.findByText('XS')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders the scopable and non localizable simple select attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeSimpleSelectCriterion
                    state={{
                        field: 'clothing_size_scopable',
                        operator: Operator.IN_LIST,
                        value: ['xs', 's'],
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

    expect(await screen.findByText('Clothing size')).toBeInTheDocument();
    expect(await screen.findByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(await screen.findByText('XS')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(screen.queryByTestId('locale')).not.toBeInTheDocument();
});

test('it renders the non scopable and localizable simple select attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeSimpleSelectCriterion
                    state={{
                        field: 'clothing_size_localizable',
                        operator: Operator.IN_LIST,
                        value: ['xs', 's'],
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

    expect(await screen.findByText('Clothing size')).toBeInTheDocument();
    expect(await screen.findByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(await screen.findByText('XS')).toBeInTheDocument();
    expect(screen.queryByTestId('channel')).not.toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders criterion with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeSimpleSelectCriterion
                    state={{
                        field: 'clothing_size_localizable_scopable',
                        operator: Operator.IN_LIST,
                        value: ['xs', 's'],
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
                <AttributeSimpleSelectCriterion
                    state={{
                        field: 'clothing_size',
                        operator: Operator.IN_LIST,
                        value: [],
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
                <AttributeSimpleSelectCriterion
                    state={{
                        field: 'clothing_size',
                        operator: Operator.IN_LIST,
                        value: ['xs', 's'],
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
    fireEvent.click(await screen.findByText(Operator.IS_EMPTY));

    expect(onChange).toHaveBeenCalledWith({
        field: 'clothing_size',
        operator: Operator.IS_EMPTY,
        value: [],
        locale: null,
        scope: null,
    });
});

test('it calls onChange when the value changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeSimpleSelectCriterion
                    state={{
                        field: 'clothing_size',
                        operator: Operator.IN_LIST,
                        value: ['xs', 's'],
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

    openDropdown('value');
    fireEvent.click(await screen.findByText('L'));

    expect(onChange).toHaveBeenCalledWith({
        field: 'clothing_size',
        operator: Operator.IN_LIST,
        value: ['xs', 's', 'l'],
        locale: null,
        scope: null,
    });
});

test('it calls onChange when the channel changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeSimpleSelectCriterion
                    state={{
                        field: 'clothing_size_scopable',
                        operator: Operator.IN_LIST,
                        value: ['xs', 's'],
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
        field: 'clothing_size_scopable',
        operator: Operator.IN_LIST,
        value: ['xs', 's'],
        locale: null,
        scope: 'print',
    });
});

test('it calls onChange when the locale changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeSimpleSelectCriterion
                    state={{
                        field: 'clothing_size_localizable',
                        operator: Operator.IN_LIST,
                        value: ['xs', 's'],
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
        field: 'clothing_size_localizable',
        operator: Operator.IN_LIST,
        value: ['xs', 's'],
        locale: 'fr_FR',
        scope: null,
    });
});
