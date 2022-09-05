jest.mock('../../hooks/useOperatorTranslator');

import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';
import {Operator} from '../../models/Operator';
import {CompletenessCriterion} from './CompletenessCriterion';

const localeUS = {code: 'en_US', label: 'English'};
const localeFR = {code: 'fr_FR', label: 'French'};
const localeDE = {code: 'de_DE', label: 'German'};
const channelPrint = {code: 'print', label: 'Print'};
const channelEcommerce = {code: 'ecommerce', label: 'E-commerce'};

const changeOperatorValueTo = (operator: string) => changeSelectValueTo('operator', operator);
const changeChannelValueTo = (channel: string) => changeSelectValueTo('scope', channel);
const changeLocaleValueTo = (locale: string) => changeSelectValueTo('locale', locale);
const changeSelectValueTo = (selector: string, value: string) => {
    const select = screen.getByTestId(selector);
    fireEvent.click(within(select).getByRole('textbox'));
    fireEvent.click(screen.getByText(value));
};

test('it renders the completeness criterion', async () => {
    fetchMock.mockResponses(
        // useChannel
        JSON.stringify(channelPrint),

        // useInfiniteChannel
        JSON.stringify([channelPrint, channelEcommerce]),

        // useChannelLocales
        JSON.stringify([localeUS, localeFR, localeDE])
    );

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CompletenessCriterion
                    state={{
                        field: 'completeness',
                        operator: Operator.LOWER_THAN,
                        value: 25,
                        locale: 'en_US',
                        scope: 'print',
                    }}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(
        await screen.findByText('akeneo_catalogs.product_selection.criteria.completeness.label')
    ).toBeInTheDocument();
    expect(await screen.findByText(Operator.LOWER_THAN)).toBeInTheDocument();
    expect(await screen.findByDisplayValue(25)).toBeInTheDocument();
    expect(await screen.findByText('Print')).toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});

test('it renders criterion with validation errors', () => {
    fetchMock.mockResponses(
        // useChannel
        JSON.stringify(channelPrint),

        // useInfiniteChannel
        JSON.stringify([channelPrint, channelEcommerce]),

        // useChannelLocales
        JSON.stringify([localeUS, localeFR, localeDE])
    );

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CompletenessCriterion
                    state={{
                        field: 'completeness',
                        operator: Operator.LOWER_THAN,
                        value: 25,
                        locale: 'en_US',
                        scope: 'print',
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
    fetchMock.mockResponses(
        // useChannel
        JSON.stringify(channelPrint),

        // useInfiniteChannel
        JSON.stringify([channelPrint, channelEcommerce]),

        // useChannelLocales
        JSON.stringify([localeUS, localeFR, localeDE])
    );

    const onRemove = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CompletenessCriterion
                    state={{
                        field: 'completeness',
                        operator: Operator.LOWER_THAN,
                        value: 25,
                        locale: 'en_US',
                        scope: 'print',
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
    fetchMock.mockResponses(
        // useChannel
        JSON.stringify(channelPrint),

        // useInfiniteChannel
        JSON.stringify([channelPrint, channelEcommerce]),

        // useChannelLocales
        JSON.stringify([localeUS, localeFR, localeDE])
    );

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CompletenessCriterion
                    state={{
                        field: 'completeness',
                        operator: Operator.LOWER_THAN,
                        value: 25,
                        locale: 'en_US',
                        scope: 'print',
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    changeOperatorValueTo(Operator.EQUALS);

    expect(onChange).toHaveBeenCalledWith({
        field: 'completeness',
        operator: Operator.EQUALS,
        value: 25,
        locale: 'en_US',
        scope: 'print',
    });
});

test('it calls onChange when the value changes', () => {
    fetchMock.mockResponses(
        // useChannel
        JSON.stringify(channelPrint),

        // useInfiniteChannel
        JSON.stringify([channelPrint, channelEcommerce]),

        // useChannelLocales
        JSON.stringify([localeUS, localeFR, localeDE])
    );

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CompletenessCriterion
                    state={{
                        field: 'completeness',
                        operator: Operator.LOWER_THAN,
                        value: 25,
                        locale: 'en_US',
                        scope: 'print',
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    const input = screen.getByTestId('value');
    fireEvent.change(input, {target: {value: 75}});

    expect(onChange).toHaveBeenCalledWith({
        field: 'completeness',
        operator: Operator.LOWER_THAN,
        value: 75,
        locale: 'en_US',
        scope: 'print',
    });
});

test('it resets completeness to 0 when any textual value is entered', () => {
    fetchMock.mockResponses(
        // useChannel
        JSON.stringify(channelPrint),

        // useInfiniteChannel
        JSON.stringify([channelPrint, channelEcommerce]),

        // useChannelLocales
        JSON.stringify([localeUS, localeFR, localeDE])
    );

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CompletenessCriterion
                    state={{
                        field: 'completeness',
                        operator: Operator.LOWER_THAN,
                        value: 25,
                        locale: 'en_US',
                        scope: 'print',
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    const input = screen.getByTestId('value');
    fireEvent.change(input, {target: {value: 'completeness_value'}});

    expect(onChange).toHaveBeenCalledWith({
        field: 'completeness',
        operator: Operator.LOWER_THAN,
        value: 0,
        locale: 'en_US',
        scope: 'print',
    });
});

test('it calls onChange when the channel changes', async () => {
    fetchMock.mockResponses(
        // useChannel
        JSON.stringify(channelPrint),

        // useInfiniteChannel
        JSON.stringify([channelPrint, channelEcommerce]),

        // useChannelLocales
        JSON.stringify([localeUS, localeFR, localeDE])
    );

    const onChange = jest.fn();

    fetchMock.mockResponses(JSON.stringify([channelPrint, channelEcommerce]));

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CompletenessCriterion
                    state={{
                        field: 'completeness',
                        operator: Operator.LOWER_THAN,
                        value: 25,
                        locale: 'en_US',
                        scope: 'print',
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );
    expect(await screen.findByText('Print')).toBeInTheDocument();

    changeChannelValueTo('E-commerce');

    expect(onChange).toHaveBeenCalledWith({
        field: 'completeness',
        operator: Operator.LOWER_THAN,
        value: 25,
        locale: null,
        scope: 'ecommerce',
    });
});

test('it calls onChange when the locale changes', async () => {
    fetchMock.mockResponses(
        // useChannel
        JSON.stringify(channelPrint),

        // useInfiniteChannel
        JSON.stringify([channelPrint, channelEcommerce]),

        // useChannelLocales
        JSON.stringify([localeUS, localeFR, localeDE])
    );

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <CompletenessCriterion
                    state={{
                        field: 'completeness',
                        operator: Operator.LOWER_THAN,
                        value: 25,
                        locale: 'en_US',
                        scope: 'print',
                    }}
                    onChange={onChange}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('English')).toBeInTheDocument();

    changeLocaleValueTo('French');

    expect(onChange).toHaveBeenCalledWith({
        field: 'completeness',
        operator: Operator.LOWER_THAN,
        value: 25,
        locale: 'fr_FR',
        scope: 'print',
    });
});
