jest.unmock('./ProductSelection');

import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductSelection} from './ProductSelection';
import {useCatalogCriteria} from './hooks/useCatalogCriteria';
import {Operator} from './models/Operator';
import {Criterion, CriterionState} from './models/Criterion';
import {AddCriterionDropdown} from './components/AddCriterionDropdown';

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
        expect.objectContaining({
            state: {
                field: 'foo',
                operator: Operator.IS_EMPTY,
            },
        }),
        {}
    );
    fireEvent.click(screen.getByText('[ToggleFooCriterionValue]'));
    expect(FooCriterionModule).toHaveBeenCalledWith(
        expect.objectContaining({
            state: {
                field: 'foo',
                operator: Operator.IS_NOT_EMPTY,
            },
        }),
        {}
    );
});

test('it updates the state when a criterion is added', () => {
    const FooCriterion = (): Criterion<CriterionState> => ({
        id: (Math.random() + 1).toString(36).substring(7),
        module: () => <div>[FooCriterion]</div>,
        state: {
            field: 'foo',
            operator: Operator.IS_EMPTY,
        },
    });

    (useCatalogCriteria as unknown as jest.MockedFunction<typeof useCatalogCriteria>).mockImplementation(() => [
        FooCriterion(),
    ]);
    (AddCriterionDropdown as unknown as jest.MockedFunction<typeof AddCriterionDropdown>).mockImplementation(
        ({onNewCriterion}) => <button onClick={() => onNewCriterion(FooCriterion())}>[AddCriterion]</button>
    );

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection id='c00a6ef5-da23-4dbe-83a2-98ccc7075890' />
        </ThemeProvider>
    );

    expect(screen.getAllByText('[FooCriterion]')).toHaveLength(1);
    fireEvent.click(screen.getByText('[AddCriterion]'));
    expect(screen.getAllByText('[FooCriterion]')).toHaveLength(2);
});

test('it updates the state when a criterion is removed', () => {
    const FooCriterion = (): Criterion<CriterionState> => ({
        id: (Math.random() + 1).toString(36).substring(7),
        module: ({onRemove}) => <button onClick={onRemove}>[RemoveFooCriteria]</button>,
        state: {
            field: 'foo',
            operator: Operator.IS_EMPTY,
        },
    });

    (useCatalogCriteria as unknown as jest.MockedFunction<typeof useCatalogCriteria>).mockImplementation(() => [
        FooCriterion(),
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection id='c00a6ef5-da23-4dbe-83a2-98ccc7075890' />
        </ThemeProvider>
    );

    expect(screen.getByText('[RemoveFooCriteria]')).toBeInTheDocument();
    fireEvent.click(screen.getByText('[RemoveFooCriteria]'));
    expect(screen.queryByText('[RemoveFooCriteria]')).not.toBeInTheDocument();
});
