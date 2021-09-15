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
    ajaxUrl: '/foo',
    processAjaxResponse: jest.fn(),
    fetchByIdentifiers: jest.fn(),
};

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <PermissionFormWidget {...props} />
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
