import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {DefaultValue} from './DefaultValue';

test('it displays a Text input when the target type is string', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue targetTypeKey={'string'} value={undefined} onChange={jest.fn()} error={undefined} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('string-default-value')).toBeInTheDocument();
});

test('it updates the default value for type string when a default value changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue
                    targetTypeKey={'string'}
                    value={'Default string value'}
                    onChange={onChange}
                    error={undefined}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('string-default-value')).toBeInTheDocument();
    expect(screen.getByDisplayValue('Default string value')).toBeInTheDocument();

    const input = screen.getByTestId('string-default-value');
    fireEvent.change(input, {target: {value: 'Updated default string value'}});

    expect(onChange).toHaveBeenCalledWith('Updated default string value');
});

test('it returns undefined value for type string when the text input is empty', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue
                    targetTypeKey={'string'}
                    value={'Default string value'}
                    onChange={onChange}
                    error={undefined}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('string-default-value')).toBeInTheDocument();
    expect(screen.getByDisplayValue('Default string value')).toBeInTheDocument();

    const input = screen.getByTestId('string-default-value');
    fireEvent.change(input, {target: {value: ''}});

    expect(onChange).toHaveBeenCalledWith(undefined);
});

test('it displays a Boolean input when the target type is boolean', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue targetTypeKey={'boolean'} value={undefined} onChange={jest.fn()} error={undefined} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('boolean-default-value')).toBeInTheDocument();
});

test('it updates the default value for type boolean when a default value changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue targetTypeKey={'boolean'} value={false} onChange={onChange} error={undefined} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    const booleanInput = screen.getByTestId('boolean-default-value');
    expect(booleanInput).toBeInTheDocument();

    expect(booleanInput.getAttribute('aria-checked')).toBe('false');
    const booleanInputTrue = screen.getByText('Yes');

    fireEvent.click(booleanInputTrue);

    expect(onChange).toHaveBeenCalledWith(true);
});

test('it returns undefined value for type boolean when clear button is clicked', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue targetTypeKey={'boolean'} value={false} onChange={onChange} error={undefined} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    const booleanInput = screen.getByTestId('boolean-default-value');
    expect(booleanInput).toBeInTheDocument();

    const booleanInputTrue = screen.getByText('Clear value');
    fireEvent.click(booleanInputTrue);

    expect(onChange).toHaveBeenCalledWith(undefined);
});

test('it displays a number input when the target type is number', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue targetTypeKey={'number'} value={undefined} onChange={jest.fn()} error={undefined} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('number-default-value')).toBeInTheDocument();
});

test('it updates the default value for type number when a default value changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue targetTypeKey={'number'} value={250} onChange={onChange} error={undefined} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('number-default-value')).toBeInTheDocument();
    expect(screen.getByDisplayValue('250')).toBeInTheDocument();

    const input = screen.getByTestId('number-default-value');
    fireEvent.change(input, {target: {value: '42'}});

    expect(onChange).toHaveBeenCalledWith(42);
});

test('it returns undefined value for type number when the number input is empty', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue targetTypeKey={'number'} value={42} onChange={onChange} error={undefined} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('number-default-value')).toBeInTheDocument();
    expect(screen.getByDisplayValue('42')).toBeInTheDocument();

    const input = screen.getByTestId('number-default-value');
    fireEvent.change(input, {target: {value: ''}});

    expect(onChange).toHaveBeenCalledWith(undefined);
});
