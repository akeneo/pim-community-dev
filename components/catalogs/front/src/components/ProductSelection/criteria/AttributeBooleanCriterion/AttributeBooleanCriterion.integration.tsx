jest.mock('../../hooks/useOperatorTranslator');

import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';
import {Operator} from '../../models/Operator';
import {AttributeBooleanCriterion} from './AttributeBooleanCriterion';

const localeUS = {code: 'en_US', label: 'English'};
const localeFR = {code: 'fr_FR', label: 'French'};
const localeDE = {code: 'de_DE', label: 'German'};

const channelEcommerce = {code: 'ecommerce', label: 'E-commerce'};
const channelPrint = {code: 'print', label: 'Print'};

const changeOperatorTo = (operator: string) => changeSelectValueTo('operator', operator);
const changeChannelTo = (channel: string) => changeSelectValueTo('scope', channel);
const changeLocaleTo = (locale: string) => changeSelectValueTo('locale', locale);
const changeInputTo = (value: string) => changeSelectValueTo('value', value);
const changeSelectValueTo = (selector: string, value: string) => {
    const select = screen.getByTestId(selector);
    fireEvent.click(within(select).getByRole('textbox'));
    fireEvent.click(screen.getByText(value));
};

beforeEach(() => {
    fetchMock.mockResponse(req => {
        switch (req.url) {
            // useAttribute
            case '/rest/catalogs/attributes/display_color_localizable_scopable':
                return Promise.resolve(
                    JSON.stringify({
                        label: 'Display Color',
                        code: 'display_color',
                        type: 'pim_catalog_boolean',
                        scopable: true,
                        localizable: true,
                    })
                );
            case '/rest/catalogs/attributes/display_color_scopable':
                return Promise.resolve(
                    JSON.stringify({
                        label: 'Display Color',
                        code: 'display_color',
                        type: 'pim_catalog_boolean',
                        scopable: true,
                        localizable: false,
                    })
                );
            case '/rest/catalogs/attributes/display_color_localizable':
                return Promise.resolve(
                    JSON.stringify({
                        label: 'Display Color',
                        code: 'display_color',
                        type: 'pim_catalog_boolean',
                        scopable: false,
                        localizable: true,
                    })
                );
            // useChannel
            case '/rest/catalogs/channels/ecommerce':
                return Promise.resolve(JSON.stringify(channelEcommerce));
            case '/rest/catalogs/channels/print':
                return Promise.resolve(JSON.stringify(channelPrint));
            // useChannelLocales
            case '/rest/catalogs/channels/ecommerce/locales':
                return Promise.resolve(JSON.stringify([localeUS, localeFR]));
            // useLocales
            case '/rest/catalogs/locales':
                return Promise.resolve(JSON.stringify([localeUS, localeFR, localeDE]));
            // useInfiniteChannels
            case '/rest/catalogs/channels?page=1&limit=20':
                return Promise.resolve(JSON.stringify([channelEcommerce, channelPrint]));
            default:
                throw Error(req.url);
        }
    });
});

test('it renders the scopable and localizable boolean attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_localizable_scopable',
                        operator: Operator.EQUALS,
                        value: true,
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

    expect(await screen.findByText('Display Color')).toBeInTheDocument();
    expect(await screen.findByText(Operator.EQUALS)).toBeInTheDocument();
    expect(await screen.findByText('akeneo_catalogs.product_selection.criteria.boolean.yes')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders the scopable and non localizable boolean attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_scopable',
                        operator: Operator.NOT_EQUAL,
                        value: false,
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

    expect(await screen.findByText('Display Color')).toBeInTheDocument();
    expect(await screen.findByText(Operator.NOT_EQUAL)).toBeInTheDocument();
    expect(await screen.findByText('akeneo_catalogs.product_selection.criteria.boolean.no')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(screen.queryByTestId('locale')).not.toBeInTheDocument();
});

test('it renders the non scopable and localizable boolean attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_localizable',
                        operator: Operator.EQUALS,
                        value: true,
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

    expect(await screen.findByText('Display Color')).toBeInTheDocument();
    expect(await screen.findByText(Operator.EQUALS)).toBeInTheDocument();
    expect(await screen.findByText('akeneo_catalogs.product_selection.criteria.boolean.yes')).toBeInTheDocument();
    expect(screen.queryByTestId('scope')).not.toBeInTheDocument();
    expect(await screen.findByText('German')).toBeInTheDocument();
});

test('it renders criterion with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_localizable_scopable',
                        operator: Operator.CONTAINS,
                        value: 'blue',
                        locale: 'kz_KZ',
                        scope: 'no_scope',
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
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_localizable_scopable',
                        operator: Operator.EQUALS,
                        value: true,
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

test('it calls onChange when the operator changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_localizable_scopable',
                        operator: Operator.EQUALS,
                        value: true,
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

    changeOperatorTo(Operator.NOT_EQUAL);

    expect(onChange).toHaveBeenCalledWith({
        field: 'display_color_localizable_scopable',
        operator: Operator.NOT_EQUAL,
        value: true,
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it resets value when the operator changes to IS_EMPTY', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_localizable_scopable',
                        operator: Operator.EQUALS,
                        value: true,
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

    changeOperatorTo(Operator.IS_EMPTY);

    expect(onChange).toHaveBeenCalledWith({
        field: 'display_color_localizable_scopable',
        operator: Operator.IS_EMPTY,
        value: null,
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it calls onChange when the value changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_localizable_scopable',
                        operator: Operator.EQUALS,
                        value: true,
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

    changeInputTo('akeneo_catalogs.product_selection.criteria.boolean.no');

    expect(onChange).toHaveBeenCalledWith({
        field: 'display_color_localizable_scopable',
        operator: Operator.EQUALS,
        value: false,
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it calls onChange when the channel changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_localizable_scopable',
                        operator: Operator.EQUALS,
                        value: true,
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

    expect(await screen.findByText('E-commerce')).toBeInTheDocument();

    changeChannelTo('Print');

    expect(onChange).toHaveBeenCalledWith({
        field: 'display_color_localizable_scopable',
        operator: Operator.EQUALS,
        value: true,
        locale: null,
        scope: 'print',
    });
});

test('it calls onChange when the locale changes', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeBooleanCriterion
                    state={{
                        field: 'display_color_localizable_scopable',
                        operator: Operator.EQUALS,
                        value: true,
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

    expect(await screen.findByText('English')).toBeInTheDocument();

    changeLocaleTo('French');

    expect(onChange).toHaveBeenCalledWith({
        field: 'display_color_localizable_scopable',
        operator: Operator.EQUALS,
        value: true,
        locale: 'fr_FR',
        scope: 'ecommerce',
    });
});
