import $ from 'jquery';
import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '@testing-library/react';
import {MultiSelectInputWithStaticOptions} from './MultiSelectInputWithStaticOptions';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

// @ts-ignore
const select2 = ($.fn.select2 = jest.fn());

const props = {
    disabled: false,
    value: [],
    onAdd: jest.fn(),
    onRemove: jest.fn(),
    onChange: jest.fn(),
    options: [],
};

test('it renders with the expected configuration', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithStaticOptions {...props} />
        </ThemeProvider>
    );
    expect(select2).toBeCalledWith({
        closeOnSelect: true,
    });
    expect(select2).toBeCalledWith('enable', true);
});

test('it renders the options', () => {
    const options = [{id: 'foo', text: 'foo'}];
    render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithStaticOptions {...props} options={options} />
        </ThemeProvider>
    );
    expect(screen.queryByText('foo')).toBeInTheDocument();
});

test('it calls onAdd when an entry is selected', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithStaticOptions {...props} />
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
            <MultiSelectInputWithStaticOptions {...props} />
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
            <MultiSelectInputWithStaticOptions {...props} />
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
            <MultiSelectInputWithStaticOptions {...props} />
        </ThemeProvider>
    );
    rerender(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithStaticOptions {...props} disabled={true} />
        </ThemeProvider>
    );
    expect(select2).toBeCalledWith('enable', false);
});

test('it has a new value when the corresponding props changes', () => {
    const options = [{id: 'foo', text: 'foo'}];

    const {rerender} = render(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithStaticOptions {...props} options={options} />
        </ThemeProvider>
    );
    rerender(
        <ThemeProvider theme={pimTheme}>
            <MultiSelectInputWithStaticOptions {...props} options={options} value={['foo']} />
        </ThemeProvider>
    );
    expect(select2).toBeCalledWith('data', [{id: 'foo', text: 'foo'}]);
});
