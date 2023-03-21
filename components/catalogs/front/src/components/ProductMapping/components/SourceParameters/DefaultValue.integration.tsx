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
                <DefaultValue
                    target={{code: 'erp_name', label: 'ERP name', type: 'string', format: null}}
                    source={{source: null, scope: null, locale: null}}
                    onChange={jest.fn()}
                    error={undefined}
                ></DefaultValue>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('string-default-value')).toBeInTheDocument();
});

test('it updates the source for type string when a default value changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue
                    target={{code: 'erp_name', label: 'ERP name', type: 'string', format: null}}
                    source={{source: null, scope: null, locale: null, default: 'Default string value'}}
                    onChange={onChange}
                    error={undefined}
                ></DefaultValue>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('string-default-value')).toBeInTheDocument();
    expect(screen.getByDisplayValue('Default string value')).toBeInTheDocument();

    const input = screen.getByTestId('string-default-value');
    fireEvent.change(input, {target: {value: 'Updated default string value'}});

    expect(onChange).toHaveBeenCalledWith({
        source: null,
        scope: null,
        locale: null,
        default: 'Updated default string value',
    });
});

test('it removes the source default value for type string when the text input is empty', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <DefaultValue
                    target={{code: 'erp_name', label: 'ERP name', type: 'string', format: null}}
                    source={{source: null, scope: null, locale: null, default: 'Default string value'}}
                    onChange={onChange}
                    error={undefined}
                ></DefaultValue>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByTestId('string-default-value')).toBeInTheDocument();
    expect(screen.getByDisplayValue('Default string value')).toBeInTheDocument();

    const input = screen.getByTestId('string-default-value');
    fireEvent.change(input, {target: {value: ''}});

    expect(onChange).toHaveBeenCalledWith({
        source: null,
        scope: null,
        locale: null,
    });
});
