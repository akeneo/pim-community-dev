import React from 'react';
import {render, screen} from '@testing-library/react';
import {fireEvent} from '@testing-library/dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {PermissionFormWidget} from './PermissionFormWidget';

jest.mock('../dependencies/translate', () => ({
    __esModule: true,
    default: (key: string) => key,
}));
jest.mock('./MultiSelectInputWithDynamicOptions', () => ({
    MultiSelectInputWithDynamicOptions: () => <input type='hidden' />,
}));
jest.mock('./MultiSelectInputWithStaticOptions', () => ({
    MultiSelectInputWithStaticOptions: () => <select />,
}));

const props = {
    selection: [],
    onAdd: jest.fn(),
    onRemove: jest.fn(),
    disabled: false,
    readOnly: false,
    allByDefaultIsSelected: false,
    onSelectAllByDefault: jest.fn(),
    onDeselectAllByDefault: jest.fn(),
    onClear: jest.fn(),
};

test('it renders the ajax version without error', () => {
    const ajax = {
        ajaxUrl: '/foo',
        processAjaxResponse: jest.fn(),
        fetchByIdentifiers: jest.fn(),
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <PermissionFormWidget {...props} ajax={ajax} />
        </ThemeProvider>
    );
});

test('it renders the select version without error', () => {
    const options = [{id: 'foo', text: 'foo'}];

    render(
        <ThemeProvider theme={pimTheme}>
            <PermissionFormWidget {...props} options={options} />
        </ThemeProvider>
    );
});

test('it can check and uncheck the checkbox', () => {
    const {rerender} = render(
        <ThemeProvider theme={pimTheme}>
            <PermissionFormWidget {...props} />
        </ThemeProvider>
    );

    const checkbox = screen.getByRole('checkbox');

    fireEvent.click(checkbox);
    expect(props.onSelectAllByDefault).toBeCalledTimes(1);

    rerender(
        <ThemeProvider theme={pimTheme}>
            <PermissionFormWidget {...props} allByDefaultIsSelected={true} />
        </ThemeProvider>
    );

    fireEvent.click(checkbox);
    expect(props.onDeselectAllByDefault).toBeCalledTimes(1);
});
