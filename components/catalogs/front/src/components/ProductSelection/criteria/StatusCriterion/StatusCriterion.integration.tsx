import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {StatusCriterion} from './StatusCriterion';
import {Operator} from '../../models/Operator';

jest.mock('../../hooks/useOperatorTranslator');

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <StatusCriterion
                state={{field: 'enabled', operator: Operator.EQUALS, value: true}}
                onChange={jest.fn()}
                onRemove={jest.fn()}
                errors={{}}
            />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_selection.criteria.status.label')).toBeInTheDocument();
    expect(screen.getByText(Operator.EQUALS)).toBeInTheDocument();
    expect(screen.getByText('akeneo_catalogs.product_selection.criteria.status.enabled')).toBeInTheDocument();
});

test('it renders inputs with validation errors', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <StatusCriterion
                state={{field: 'enabled', operator: Operator.EQUALS, value: true}}
                onChange={jest.fn()}
                onRemove={jest.fn()}
                errors={{
                    field: undefined,
                    operator: 'Invalid operator.',
                    value: 'Invalid value.',
                }}
            />
        </ThemeProvider>
    );

    expect(screen.getByText('Invalid operator.')).toBeInTheDocument();
    expect(screen.getByText('Invalid value.')).toBeInTheDocument();
});

test('it calls onChange when the operator changes', () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <StatusCriterion
                state={{field: 'enabled', operator: Operator.EQUALS, value: true}}
                onChange={onChange}
                onRemove={jest.fn()}
                errors={{}}
            />
        </ThemeProvider>
    );

    const container = screen.getByTestId('operator');

    fireEvent.click(within(container).getByRole('textbox'));
    fireEvent.click(screen.getByText(Operator.NOT_EQUAL));

    expect(onChange).toHaveBeenCalledWith({
        field: 'enabled',
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
                errors={{}}
            />
        </ThemeProvider>
    );

    const container = screen.getByTestId('value');

    fireEvent.click(within(container).getByRole('textbox'));
    fireEvent.click(screen.getByText('akeneo_catalogs.product_selection.criteria.status.disabled'));

    expect(onChange).toHaveBeenCalledWith({
        field: 'enabled',
        operator: Operator.EQUALS,
        value: false,
    });
});
