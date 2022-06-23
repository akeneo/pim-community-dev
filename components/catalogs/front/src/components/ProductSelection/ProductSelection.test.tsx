import {AnyCriterionState} from './models/Criteria';

jest.unmock('./ProductSelection');

import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductSelection} from './ProductSelection';
import {Operator} from './models/Operator';
import {Criterion, CriterionState} from './models/Criterion';
import {AddCriterionDropdown} from './components/AddCriterionDropdown';

test('it renders the empty message', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={[]} setCriteria={jest.fn()} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Empty]')).toBeInTheDocument();
});

test('it renders a list of criteria', () => {
    const criteria = [
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
    ];
    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={criteria} setCriteria={jest.fn()} />
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

    const criteria = [
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
    ];
    const setCriteria = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={criteria} setCriteria={setCriteria} />
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('[ToggleFooCriterionValue]'));
    expect(setCriteria).toHaveBeenCalledWith([
        expect.objectContaining({
            state: {
                field: 'foo',
                operator: Operator.IS_NOT_EMPTY,
            },
        }),
        expect.objectContaining({
            state: {
                field: 'bar',
                operator: Operator.IS_EMPTY,
            },
        }),
    ]);
});

test('it updates the state when a criterion is added', () => {
    const FooCriterion = (): Criterion<any> => ({
        id: (Math.random() + 1).toString(36).substring(7),
        module: () => <div>[FooCriterion]</div>,
        state: {
            field: 'foo',
            operator: Operator.IS_EMPTY,
        },
    });
    (AddCriterionDropdown as unknown as jest.MockedFunction<typeof AddCriterionDropdown>).mockImplementation(
        ({onNewCriterion}) => <button onClick={() => onNewCriterion(FooCriterion())}>[AddCriterion]</button>
    );
    const setCriteria = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={[]} setCriteria={setCriteria} />
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('[AddCriterion]'));
    expect(setCriteria).toHaveBeenCalledWith([
        expect.objectContaining({
            state: {
                field: 'foo',
                operator: Operator.IS_EMPTY,
            },
        }),
    ]);
});

test('it updates the state when a criterion is removed', () => {
    const FooCriterion = (): Criterion<any> => ({
        id: (Math.random() + 1).toString(36).substring(7),
        module: ({onRemove}) => <button onClick={onRemove}>[RemoveFooCriteria]</button>,
        state: {
            field: 'foo',
            operator: Operator.IS_EMPTY,
        },
    });
    const setCriteria = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={[FooCriterion()]} setCriteria={setCriteria} />
        </ThemeProvider>
    );

    expect(screen.getByText('[RemoveFooCriteria]')).toBeInTheDocument();
    fireEvent.click(screen.getByText('[RemoveFooCriteria]'));
    expect(setCriteria).toHaveBeenCalledWith([]);
});
