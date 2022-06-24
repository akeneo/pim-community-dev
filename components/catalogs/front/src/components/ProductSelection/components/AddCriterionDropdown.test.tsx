jest.unmock('./AddCriterionDropdown');

import React from 'react';
import {act, render, screen, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AddCriterionDropdown} from './AddCriterionDropdown';
import StatusCriterion, {StatusCriterionState} from '../criteria/StatusCriterion';
import {Operator} from '../models/Operator';
import {Criterion} from '../models/Criteria';

test('it renders without error', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <AddCriterionDropdown onNewCriterion={jest.fn()} />
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_selection.add_criteria.label')).toBeInTheDocument();
});

test('it opens the dropdown and adds a criterion', () => {
    (StatusCriterion as jest.Mock).mockImplementation(
        (): Criterion<StatusCriterionState> => ({
            id: 'abc6',
            module: () => null,
            state: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            },
        })
    );

    const handleNewCriterion = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <AddCriterionDropdown onNewCriterion={handleNewCriterion} />
        </ThemeProvider>
    );

    act(() => userEvent.click(screen.getByText('akeneo_catalogs.product_selection.add_criteria.label')));
    act(() => userEvent.click(screen.getByText('akeneo_catalogs.product_selection.criteria.status.label')));

    expect(handleNewCriterion).toHaveBeenCalledWith({
        id: 'abc6',
        module: expect.any(Function),
        state: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    });
});

test('it opens and closes the dropdown', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <AddCriterionDropdown onNewCriterion={jest.fn()} />
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('akeneo_catalogs.product_selection.add_criteria.label'));
    expect(screen.getByText('akeneo_catalogs.product_selection.add_criteria.section_system')).toBeInTheDocument();
    fireEvent.click(screen.getByTestId('backdrop'));
    expect(screen.queryByText('akeneo_catalogs.product_selection.add_criteria.section_system')).not.toBeInTheDocument();
});

test('it opens and searches in the options', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <AddCriterionDropdown onNewCriterion={jest.fn()} />
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('akeneo_catalogs.product_selection.add_criteria.label'));
    expect(screen.getByText('akeneo_catalogs.product_selection.add_criteria.section_system')).toBeInTheDocument();
    fireEvent.change(screen.getByRole('textbox'), {target: {value: 'not_a_valid_option'}});
    expect(screen.queryByText('akeneo_catalogs.product_selection.add_criteria.section_system')).not.toBeInTheDocument();
});
