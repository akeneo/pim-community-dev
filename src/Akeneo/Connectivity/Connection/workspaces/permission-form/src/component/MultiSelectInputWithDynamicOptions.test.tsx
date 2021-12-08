import $ from 'jquery';
import React from 'react';
import {render, screen} from '@testing-library/react';
import {MultiSelectInputWithDynamicOptions} from './MultiSelectInputWithDynamicOptions';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

// @ts-ignore
const select2 = ($.fn.select2 = jest.fn());

const props = {
    url: '/foo',
    fetchByIdentifiers: jest.fn(),
    processResults: jest.fn(),
    disabled: false,
    value: [],
    onAdd: jest.fn(),
    onRemove: jest.fn(),
    onChange: jest.fn(),
};

test('it renders with the expected configuration', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithDynamicOptions {...props} />
        </ThemeProvider>
    );
    expect(select2).toBeCalledWith({
        ajax: {
            cache: true,
            data: undefined,
            dataType: 'json',
            quietMillis: 250,
            results: props.processResults,
            url: props.url,
        },
        closeOnSelect: true,
        initSelection: expect.any(Function),
        multiple: true,
    });
    expect(select2).toBeCalledWith('enable', true);
});

test('it calls onAdd when an entry is selected', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithDynamicOptions {...props} />
        </ThemeProvider>
    );
    const input = screen.getByTestId('select2');
    // @ts-ignore
    $(input).trigger({
        type: 'change',
        // @ts-ignore
        val: ['foo'],
        // @ts-ignore
        added: {
            id: 'foo',
            text: 'Foo',
        },
    });
    expect(props.onAdd).toBeCalledWith('foo');
});

test('it calls onRemove when an entry is deselected', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithDynamicOptions {...props} />
        </ThemeProvider>
    );
    const input = screen.getByTestId('select2');
    $(input).trigger({
        type: 'change',
        // @ts-ignore
        val: [],
        // @ts-ignore
        removed: {
            id: 'foo',
            text: 'Foo',
        },
    });
    expect(props.onRemove).toBeCalledWith('foo');
});

test('it calls onChange when the selection changes', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithDynamicOptions {...props} />
        </ThemeProvider>
    );
    const input = screen.getByTestId('select2');
    $(input).trigger({
        type: 'change',
        // @ts-ignore
        val: ['foo'],
    });
    expect(props.onChange).toBeCalledWith(['foo']);
});

test('it is disabled when the corresponding props changes', () => {
    const {rerender} = render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithDynamicOptions {...props} />
        </ThemeProvider>
    );
    rerender(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithDynamicOptions {...props} disabled={true} />
        </ThemeProvider>
    );
    expect(select2).toBeCalledWith('enable', false);
});

test('it has a new value when the corresponding props changes', () => {
    const {rerender} = render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithDynamicOptions {...props} />
        </ThemeProvider>
    );
    rerender(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithDynamicOptions {...props} value={['foo']} />
        </ThemeProvider>
    );
    expect(select2).toBeCalledWith('val', ['foo']);
});
