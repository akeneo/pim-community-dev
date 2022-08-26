jest.mock('../../hooks/useOperatorTranslator');

import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';
import {Operator} from '../../models/Operator';
import {AttributeTextCriterion} from './AttributeTextCriterion';

const localeUS = {code: 'en_US', label: 'English'};
const localeFR = {code: 'fr_FR', label: 'French'};
const localeDE = {code: 'de_DE', label: 'German'};

const channelEcommerce = {code: 'ecommerce', label: 'E-commerce'};
const channelPrint = {code: 'print', label: 'Print'};

const changeOperatorTo = (operator: string) => changeSelectValueTo('operator', operator);
const changeChannelTo = (channel: string) => changeSelectValueTo('scope', channel);
const changeLocaleTo = (locale: string) => changeSelectValueTo('locale', locale);
const changeSelectValueTo = (selector: string, value: string) => {
    const select = screen.getByTestId(selector);
    fireEvent.click(within(select).getByRole('textbox'));
    fireEvent.click(screen.getByText(value));
};

beforeEach(() => {
    fetchMock.mockResponse(req => {
        switch (req.url) {
            // useAttribute
            case '/rest/catalogs/attributes/name_localizable_scopable':
                return Promise.resolve(
                    JSON.stringify({
                        label: 'Name',
                        code: 'name',
                        type: 'pim_catalog_text',
                        scopable: true,
                        localizable: true,
                    })
                );
            case '/rest/catalogs/attributes/name_scopable':
                return Promise.resolve(
                    JSON.stringify({
                        label: 'Name',
                        code: 'name',
                        type: 'pim_catalog_text',
                        scopable: true,
                        localizable: false,
                    })
                );
            case '/rest/catalogs/attributes/name_localizable':
                return Promise.resolve(
                    JSON.stringify({
                        label: 'Name',
                        code: 'name',
                        type: 'pim_catalog_text',
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

test('it renders the scopable and localizable text attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextCriterion
                    state={{
                        field: 'name_localizable_scopable',
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

    expect(await screen.findByText('Name')).toBeInTheDocument();
    expect(await screen.findByText(Operator.CONTAINS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue('blue')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders the scopable and non localizable text attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextCriterion
                    state={{
                        field: 'name_scopable',
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

    expect(await screen.findByText('Name')).toBeInTheDocument();
    expect(await screen.findByText(Operator.CONTAINS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue('blue')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(screen.queryByTestId('locale')).not.toBeInTheDocument();
});

test('it renders the non scopable and localizable text attribute criterion', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextCriterion
                    state={{
                        field: 'name_localizable',
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

    expect(await screen.findByText('Name')).toBeInTheDocument();
    expect(await screen.findByText(Operator.CONTAINS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue('blue')).toBeInTheDocument();
    expect(screen.queryByTestId('scope')).not.toBeInTheDocument();
    expect(await screen.findByText('German')).toBeInTheDocument();
});

test('it renders criterion with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextCriterion
                    state={{
                        field: 'name_localizable_scopable',
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
                <AttributeTextCriterion
                    state={{
                        field: 'name_localizable_scopable',
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

test('it calls onChange when the operator changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextCriterion
                    state={{
                        field: 'name_localizable_scopable',
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

    changeOperatorTo(Operator.EQUALS);

    expect(onChange).toHaveBeenCalledWith({
        field: 'name_localizable_scopable',
        operator: Operator.EQUALS,
        value: 'blue',
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it resets value when the operator changes to IS_EMPTY', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextCriterion
                    state={{
                        field: 'name_localizable_scopable',
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

    changeOperatorTo(Operator.IS_EMPTY);

    expect(onChange).toHaveBeenCalledWith({
        field: 'name_localizable_scopable',
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
                <AttributeTextCriterion
                    state={{
                        field: 'name_localizable_scopable',
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
        field: 'name_localizable_scopable',
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
                <AttributeTextCriterion
                    state={{
                        field: 'name_localizable_scopable',
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

    expect(await screen.findByText('E-commerce')).toBeInTheDocument();

    changeChannelTo('Print');

    expect(onChange).toHaveBeenCalledWith({
        field: 'name_localizable_scopable',
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
                <AttributeTextCriterion
                    state={{
                        field: 'name_localizable_scopable',
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

    expect(await screen.findByText('English')).toBeInTheDocument();

    changeLocaleTo('French');

    expect(onChange).toHaveBeenCalledWith({
        field: 'name_localizable_scopable',
        operator: Operator.CONTAINS,
        value: 'blue',
        locale: 'fr_FR',
        scope: 'ecommerce',
    });
});
