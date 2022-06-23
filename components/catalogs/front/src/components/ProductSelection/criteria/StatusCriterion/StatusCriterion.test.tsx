jest.unmock('./StatusCriterion');

import React from 'react';
import {act, render, screen, within} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {StatusCriterion} from './StatusCriterion';
import {Operator} from '../../models/Operator';

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <StatusCriterion
                state={{field: 'enabled', operator: Operator.EQUALS, value: true}}
                onChange={jest.fn()}
                onRemove={jest.fn()}
            />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_selection.criteria.status.label')).toBeInTheDocument();
    expect(screen.getByText(Operator.EQUALS)).toBeInTheDocument();
    expect(screen.getByText('akeneo_catalogs.product_selection.criteria.status.enabled')).toBeInTheDocument();
});

test('it calls onChange when the operator changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <StatusCriterion
                state={{field: 'enabled', operator: Operator.EQUALS, value: true}}
                onChange={onChange}
                onRemove={jest.fn()}
            />
        </ThemeProvider>
    );

    const container = screen.getByTestId('operator');

    act(() => userEvent.click(within(container).getByRole('textbox')));
    act(() => userEvent.click(screen.getByText(Operator.NOT_EQUAL)));

    expect(onChange).toHaveBeenCalledWith({
        field: 'status',
        operator: Operator.NOT_EQUAL,
        value: true,
    });
});

test('it calls onChange when the value changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <StatusCriterion
                state={{field: 'enabled', operator: Operator.EQUALS, value: true}}
                onChange={onChange}
                onRemove={jest.fn()}
            />
        </ThemeProvider>
    );

    const container = screen.getByTestId('value');

    act(() => userEvent.click(within(container).getByRole('textbox')));
    act(() => userEvent.click(screen.getByText('akeneo_catalogs.product_selection.criteria.status.disabled')));

    expect(onChange).toHaveBeenCalledWith({
        field: 'status',
        operator: Operator.EQUALS,
        value: false,
    });
});
