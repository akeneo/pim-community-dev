import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {mockFetchResponses} from '../../../../../tests/mockFetchResponses';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import {Operator} from '../../models/Operator';
import {AttributeMultiSelectCriterion} from './AttributeMultiSelectCriterion';

jest.mock('../../hooks/useOperatorTranslator');

const ECOMMERCE = {code: 'ecommerce', label: 'E-commerce'};
const PRINT = {code: 'print', label: 'Print'};

const EN = {code: 'en_US', label: 'English'};
const FR = {code: 'fr_FR', label: 'French'};
const DE = {code: 'de_DE', label: 'German'};

const COTTON = {code: 'cotton', label: 'Cotton'};
const LEATHER = {code: 'leather', label: 'Leather'};
const POLYESTER = {code: 'polyester', label: 'Polyester'};
const WOOL = {code: 'wool', label: 'Wool'};

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
            url: '/rest/catalogs/attributes/materials_localizable_scopable',
            json: {
                code: 'materials_localizable_scopable',
                label: 'Materials',
                type: 'pim_catalog_multiselect',
                scopable: true,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/materials_localizable_scopable/options?locale=en_US&codes=cotton%2Cleather&search=&page=1&limit=20',
            json: [COTTON, LEATHER],
        },
        {
            url: '/rest/catalogs/attributes/materials_localizable_scopable/options?locale=en_US&codes=&search=&page=1&limit=20',
            json: [COTTON, LEATHER, POLYESTER, WOOL],
        },
        {
            url: '/rest/catalogs/attributes/materials_scopable',
            json: {
                code: 'materials_scopable',
                label: 'Materials',
                type: 'pim_catalog_multiselect',
                scopable: true,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/materials_scopable/options?locale=en_US&codes=cotton%2Cleather&search=&page=1&limit=20',
            json: [COTTON, LEATHER],
        },
        {
            url: '/rest/catalogs/attributes/materials_scopable/options?locale=en_US&codes=&search=&page=1&limit=20',
            json: [COTTON, LEATHER, POLYESTER, WOOL],
        },
        {
            url: '/rest/catalogs/attributes/materials_localizable',
            json: {
                code: 'materials_localizable',
                label: 'Materials',
                type: 'pim_catalog_multiselect',
                scopable: false,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes/materials_localizable/options?locale=en_US&codes=cotton%2Cleather&search=&page=1&limit=20',
            json: [COTTON, LEATHER],
        },
        {
            url: '/rest/catalogs/attributes/materials_localizable/options?locale=en_US&codes=&search=&page=1&limit=20',
            json: [COTTON, LEATHER, POLYESTER, WOOL],
        },
        {
            url: '/rest/catalogs/attributes/materials',
            json: {
                code: 'materials',
                label: 'Materials',
                type: 'pim_catalog_multiselect',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/materials/options?locale=en_US&codes=cotton%2Cleather&search=&page=1&limit=20',
            json: [COTTON, LEATHER],
        },
        {
            url: '/rest/catalogs/attributes/materials/options?locale=en_US&codes=&search=&page=1&limit=20',
            json: [COTTON, LEATHER, POLYESTER, WOOL],
        },
    ]);
});

test('it renders the scopable and localizable attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeMultiSelectCriterion
                    state={{
                        field: 'materials_localizable_scopable',
                        operator: Operator.IN_LIST,
                        value: ['cotton', 'leather'],
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

    expect(await screen.findByText('Materials')).toBeInTheDocument();
    expect(await screen.findByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(await screen.findByText('Cotton')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders the scopable and non localizable attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeMultiSelectCriterion
                    state={{
                        field: 'materials_scopable',
                        operator: Operator.IN_LIST,
                        value: ['cotton', 'leather'],
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

    expect(await screen.findByText('Materials')).toBeInTheDocument();
    expect(await screen.findByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(await screen.findByText('Cotton')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(screen.queryByTestId('locale')).not.toBeInTheDocument();
});

test('it renders the non scopable and localizable attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeMultiSelectCriterion
                    state={{
                        field: 'materials_localizable',
                        operator: Operator.IN_LIST,
                        value: ['cotton', 'leather'],
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

    expect(await screen.findByText('Materials')).toBeInTheDocument();
    expect(await screen.findByText(Operator.IN_LIST)).toBeInTheDocument();
    expect(await screen.findByText('Cotton')).toBeInTheDocument();
    expect(screen.queryByTestId('channel')).not.toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders criterion with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeMultiSelectCriterion
                    state={{
                        field: 'materials_localizable_scopable',
                        operator: Operator.IN_LIST,
                        value: ['cotton', 'leather'],
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
                <AttributeMultiSelectCriterion
                    state={{
                        field: 'materials',
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
                <AttributeMultiSelectCriterion
                    state={{
                        field: 'materials',
                        operator: Operator.IN_LIST,
                        value: ['cotton', 'leather'],
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
        field: 'materials',
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
                <AttributeMultiSelectCriterion
                    state={{
                        field: 'materials',
                        operator: Operator.IN_LIST,
                        value: ['cotton', 'leather'],
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
    fireEvent.click(await screen.findByText('Wool'));

    expect(onChange).toHaveBeenCalledWith({
        field: 'materials',
        operator: Operator.IN_LIST,
        value: ['cotton', 'leather', 'wool'],
        locale: null,
        scope: null,
    });
});

test('it calls onChange when the channel changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeMultiSelectCriterion
                    state={{
                        field: 'materials_scopable',
                        operator: Operator.IN_LIST,
                        value: ['cotton', 'leather'],
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
        field: 'materials_scopable',
        operator: Operator.IN_LIST,
        value: ['cotton', 'leather'],
        locale: null,
        scope: 'print',
    });
});

test('it calls onChange when the locale changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeMultiSelectCriterion
                    state={{
                        field: 'materials_localizable',
                        operator: Operator.IN_LIST,
                        value: ['cotton', 'leather'],
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
        field: 'materials_localizable',
        operator: Operator.IN_LIST,
        value: ['cotton', 'leather'],
        locale: 'fr_FR',
        scope: null,
    });
});
