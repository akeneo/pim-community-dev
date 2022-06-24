jest.unmock('./ProductSelection');

import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {mocked} from 'ts-jest/utils';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductSelection} from './ProductSelection';
import {Operator} from './models/Operator';
import {AddCriterionDropdown} from './components/AddCriterionDropdown';
import {StatusCriterionState} from './criteria/StatusCriterion';
import {StatusCriterion} from './criteria/StatusCriterion/types';
import {Criterion} from './models/Criteria';

test('it renders the empty message', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={[]} setCriteria={jest.fn()} onChange={jest.fn()} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Empty]')).toBeInTheDocument();
});

test('it renders a list of criteria', () => {
    const criterion1: StatusCriterion = {
        id: 'da23',
        module: () => <div>[FooCriterion]</div>,
        state: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    };
    const criterion2: StatusCriterion = {
        id: '4dbe',
        module: () => <div>[BarCriterion]</div>,
        state: {
            field: 'enabled',
            operator: Operator.NOT_EQUAL,
            value: false,
        },
    };
    const criteria = [criterion1, criterion2];

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={criteria} setCriteria={jest.fn()} onChange={jest.fn()} />
        </ThemeProvider>
    );

    expect(screen.getByText('[FooCriterion]')).toBeInTheDocument();
    expect(screen.getByText('[BarCriterion]')).toBeInTheDocument();
});

test('it updates the state when a criterion changes', () => {
    const FooCriterionModule = jest.fn(({onChange}) => (
        <button onClick={() => onChange({field: 'enabled', operator: Operator.NOT_EQUAL, value: true})}>
            [ToggleFooCriterionValue]
        </button>
    ));

    const criterion1: StatusCriterion = {
        id: 'foo',
        module: FooCriterionModule,
        state: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    };
    const criterion2: StatusCriterion = {
        id: 'bar',
        module: () => <div>[BarCriterion]</div>,
        state: {
            field: 'enabled',
            operator: Operator.NOT_EQUAL,
            value: false,
        },
    };
    const criteria = [criterion1, criterion2];
    const setCriteria = jest.fn();
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={criteria} setCriteria={setCriteria} onChange={onChange} />
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('[ToggleFooCriterionValue]'));
    expect(setCriteria).toHaveBeenCalledWith([
        expect.objectContaining({
            state: {
                field: 'enabled',
                operator: Operator.NOT_EQUAL,
                value: true,
            },
        }),
        expect.objectContaining({
            state: {
                field: 'enabled',
                operator: Operator.NOT_EQUAL,
                value: false,
            },
        }),
    ]);
    expect(onChange).toHaveBeenCalledWith(true);
});

test('it updates the state when a criterion is added', () => {
    const FooCriterion = (): Criterion<StatusCriterionState> => ({
        id: (Math.random() + 1).toString(36).substring(7),
        module: () => <div>[FooCriterion]</div>,
        state: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    });
    mocked(AddCriterionDropdown).mockImplementation(({onNewCriterion}) => (
        <button onClick={() => onNewCriterion(FooCriterion())}>[AddCriterion]</button>
    ));
    const setCriteria = jest.fn();
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={[]} setCriteria={setCriteria} onChange={onChange} />
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('[AddCriterion]'));
    expect(setCriteria).toHaveBeenCalledWith([
        expect.objectContaining({
            state: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            },
        }),
    ]);
    expect(onChange).toHaveBeenCalledWith(true);
});

test('it updates the state when a criterion is removed', () => {
    const FooCriterion = (): Criterion<StatusCriterionState> => ({
        id: (Math.random() + 1).toString(36).substring(7),
        module: ({onRemove}) => <button onClick={onRemove}>[RemoveFooCriteria]</button>,
        state: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    });
    const setCriteria = jest.fn();
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={[FooCriterion()]} setCriteria={setCriteria} onChange={onChange} />
        </ThemeProvider>
    );

    expect(screen.getByText('[RemoveFooCriteria]')).toBeInTheDocument();
    fireEvent.click(screen.getByText('[RemoveFooCriteria]'));
    expect(setCriteria).toHaveBeenCalledWith([]);
    expect(onChange).toHaveBeenCalledWith(true);
});
