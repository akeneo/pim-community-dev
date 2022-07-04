jest.unmock('./ProductSelection');
jest.unmock('./reducers/ProductSelectionReducer');
jest.unmock('./contexts/ProductSelectionContext');

import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {mocked} from 'ts-jest/utils';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductSelection} from './ProductSelection';
import {Operator} from './models/Operator';
import {StatusCriterionState} from './criteria/StatusCriterion';
import {useProductSelectionContext} from './contexts/ProductSelectionContext';
import {ProductSelectionActions} from './reducers/ProductSelectionReducer';
import {Criterion} from './components/Criterion';

test('it renders the empty message', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={{}} onChange={jest.fn()} errors={{}} />
        </ThemeProvider>
    );

    expect(screen.getByText('[Empty]')).toBeInTheDocument();
});

test('it renders a list of criteria', () => {
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
            <ProductSelection criteria={criteria} onChange={jest.fn()} errors={{}} />
        </ThemeProvider>
    );

    expect(screen.getAllByText('[criterion:enabled]')).toHaveLength(2);
});

test('it updates the state when a criterion changes', () => {
    mocked(Criterion).mockImplementation(({id, state}) => {
        const dispatch = useProductSelectionContext();

        const toggle = () =>
            dispatch({
                type: ProductSelectionActions.UPDATE_CRITERION,
                id: id,
                state: {
                    ...state,
                    value: !state.value,
                },
            });

        return <button onClick={toggle}>[ToggleCriterionValue]</button>;
    });

    const onChange = jest.fn();

    const criteria = {
        a: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        } as StatusCriterionState,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelection criteria={criteria} onChange={onChange} errors={{}} />
        </ThemeProvider>
    );

    fireEvent.click(screen.getByText('[ToggleCriterionValue]'));
    expect(onChange).toHaveBeenCalledWith({
        a: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: false,
        },
    });
});
