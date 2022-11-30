import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductSelection} from './ProductSelection';
import {Operator} from './models/Operator';
import {generateRandomId} from './utils/generateRandomId';
import {mocked} from 'ts-jest/utils';
import {StatusCriterionState} from './criteria/StatusCriterion';
import {QueryClient, QueryClientProvider} from 'react-query';

jest.mock('./utils/generateRandomId');

const MAX_CRITERIA_PER_CATALOG = 25;

test('it display an empty message if there is no criteria', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={{}} onChange={jest.fn()} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_selection.empty')).toBeInTheDocument();
});

test('it renders a list of criteria', async () => {
    const criteria = {
        a: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        } as StatusCriterionState,
        b: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: false,
        } as StatusCriterionState,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={jest.fn()} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findAllByText('akeneo_catalogs.product_selection.criteria.status.label')).toHaveLength(2);
});

test('it updates the state when a criterion is added', async () => {
    mocked(generateRandomId).mockReturnValue('a');

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={{}} onChange={onChange} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByText('akeneo_catalogs.product_selection.add_criteria.label'));
    fireEvent.click(await screen.findByText('akeneo_catalogs.product_selection.criteria.status.label'));

    expect(await screen.findByText('akeneo_catalogs.product_selection.criteria.status.label')).toBeInTheDocument();
    expect(onChange).toHaveBeenCalledWith({
        a: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    });
});

test('it updates the state when a criterion changes', async () => {
    const criteria = {
        a: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        } as StatusCriterionState,
    };
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={onChange} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    const StatusValueInput = await screen.findByTestId('value');

    fireEvent.click(within(StatusValueInput).getByRole('textbox'));
    fireEvent.click(screen.getByText('akeneo_catalogs.product_selection.criteria.status.disabled'));

    expect(onChange).toHaveBeenCalledWith({
        a: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: false,
        },
    });
});

test('it updates the state when a criterion is removed', async () => {
    const criteria = {
        a: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        } as StatusCriterionState,
    };
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={onChange} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByTitle('remove'));

    expect(onChange).toHaveBeenCalledWith({});
});

test('it shows a warning and lock the add button when the criteria limit is reached', async () => {
    const criteria = [];
    for (let criterionIndex = 0; criterionIndex < MAX_CRITERIA_PER_CATALOG; criterionIndex++) {
        criteria[criterionIndex] = {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        };
    }

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={onChange} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByText('akeneo_catalogs.product_selection.criteria.max_reached')).toBeInTheDocument();
    expect(await screen.findByText('akeneo_catalogs.product_selection.add_criteria.label')).toBeDisabled();
});

test("it doesn't show a warning and doesn't lock the add button when the criteria limit is not reached", async () => {
    const criteria = [];
    for (let criterionIndex = 0; criterionIndex < MAX_CRITERIA_PER_CATALOG - 1; criterionIndex++) {
        criteria[criterionIndex] = {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        };
    }

    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductSelection criteria={criteria} onChange={onChange} errors={{}} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByText('akeneo_catalogs.product_selection.add_criteria.label')).toBeEnabled();
    expect(screen.queryAllByText('akeneo_catalogs.product_selection.criteria.max_reached').length).toBe(0);
});
