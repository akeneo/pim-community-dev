jest.unmock('./Criterion');

import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {mocked} from 'ts-jest/utils';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Criterion} from './Criterion';
import {StatusCriterionState} from '../criteria/StatusCriterion';
import {Operator} from '../models/Operator';
import {useCriteriaRegistry} from '../hooks/useCriteriaRegistry';
import {AnyCriterion, AnyCriterionState} from '../models/Criterion';
import {ProductSelectionContext} from '../contexts/ProductSelectionContext';
import {ProductSelectionActions} from '../reducers/ProductSelectionReducer';

const mockGetCriterionByField = () =>
    Promise.resolve({
        component: ({state, onChange, onRemove}) => {
            const toggle = () => {
                onChange({
                    ...state,
                    value: !state.value,
                });
            };

            return (
                <div>
                    <div>[Component]</div>
                    <button onClick={toggle}>[ToggleCriterionValue]</button>
                    <button onClick={onRemove}>[RemoveCriterion]</button>
                </div>
            );
        },
        factory: (): AnyCriterionState => ({
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        }),
    } as AnyCriterion);

mocked(useCriteriaRegistry).mockImplementation(() => ({
    system: [],
    getCriterionByField: mockGetCriterionByField,
}));

const state: StatusCriterionState = {
    field: 'enabled',
    operator: Operator.EQUALS,
    value: true,
};
const errors = {
    field: null,
    operator: null,
    value: null,
};

test('it renders without error', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Criterion id='a' state={state} errors={errors} />
        </ThemeProvider>
    );

    expect(await screen.findByText('[Component]')).toBeInTheDocument();
});

test('it dispatches the update when it changes', async () => {
    const dispatch = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelectionContext.Provider value={dispatch}>
                <Criterion id='a' state={state} errors={errors} />
            </ProductSelectionContext.Provider>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByText('[ToggleCriterionValue]'));
    expect(dispatch).toHaveBeenCalledWith(
        expect.objectContaining({
            type: ProductSelectionActions.UPDATE_CRITERION,
            id: 'a',
            state: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: false,
            },
        })
    );
});

test('it dispatches the removal', async () => {
    const dispatch = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <ProductSelectionContext.Provider value={dispatch}>
                <Criterion id='a' state={state} errors={errors} />
            </ProductSelectionContext.Provider>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByText('[RemoveCriterion]'));
    expect(dispatch).toHaveBeenCalledWith(
        expect.objectContaining({
            type: ProductSelectionActions.REMOVE_CRITERION,
            id: 'a',
        })
    );
});
