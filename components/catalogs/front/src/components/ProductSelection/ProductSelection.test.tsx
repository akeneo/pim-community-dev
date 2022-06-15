jest.unmock('./ProductSelection');

import userEvent from '@testing-library/user-event';
import React from 'react';
import {act, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductSelection} from './ProductSelection';
import {useCatalogCriteria} from './hooks/useCatalogCriteria';
import {Operator} from './models/Operator';

test('it renders the empty message', () => {
    (useCatalogCriteria as unknown as jest.MockedFunction<typeof useCatalogCriteria>).mockImplementation(() => []);

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection id='c00a6ef5-da23-4dbe-83a2-98ccc7075890' />
        </ThemeProvider>
    );

    expect(screen.getByText('[Empty]')).toBeInTheDocument();
});

test('it renders a list of criteria', () => {
    (useCatalogCriteria as unknown as jest.MockedFunction<typeof useCatalogCriteria>).mockImplementation(() => [
        {
            id: 'da23',
            module: () => <div>[FooCriterion]</div>,
            state: {
                field: 'foo',
                operator: Operator.IS_EMPTY,
            },
        },
        {
            id: '4dbe',
            module: () => <div>[BarCriterion]</div>,
            state: {
                field: 'bar',
                operator: Operator.IS_EMPTY,
            },
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection id='c00a6ef5-da23-4dbe-83a2-98ccc7075890' />
        </ThemeProvider>
    );

    expect(screen.getByText('[FooCriterion]')).toBeInTheDocument();
    expect(screen.getByText('[BarCriterion]')).toBeInTheDocument();
});

test('it updates the state when a criterion changes', () => {
    const FooCriterionModule = jest.fn(({onChange}) => (
        <button onClick={() => onChange({field: 'foo', operator: Operator.IS_NOT_EMPTY})}>
            [ToggleFooCriterionValue]
        </button>
    ));

    (useCatalogCriteria as unknown as jest.MockedFunction<typeof useCatalogCriteria>).mockImplementation(() => [
        {
            id: 'foo',
            module: FooCriterionModule,
            state: {
                field: 'foo',
                operator: Operator.IS_EMPTY,
            },
        },
        {
            id: 'bar',
            module: () => <div>[BarCriterion]</div>,
            state: {
                field: 'bar',
                operator: Operator.IS_EMPTY,
            },
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection id='c00a6ef5-da23-4dbe-83a2-98ccc7075890' />
        </ThemeProvider>
    );

    expect(FooCriterionModule).toHaveBeenCalledWith(
        {
            onChange: expect.any(Function),
            state: {
                field: 'foo',
                operator: Operator.IS_EMPTY,
            },
        },
        {}
    );

    act(() => userEvent.click(screen.getByText('[ToggleFooCriterionValue]')));

    expect(FooCriterionModule).toHaveBeenCalledWith(
        {
            onChange: expect.any(Function),
            state: {
                field: 'foo',
                operator: Operator.IS_NOT_EMPTY,
            },
        },
        {}
    );
});
