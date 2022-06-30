jest.unmock('./ProductSelectionReducer');

import {
    ProductSelectionAction,
    ProductSelectionActions,
    ProductSelectionReducer,
    ProductSelectionState,
} from './ProductSelectionReducer';
import {Operator} from '../models/Operator';

const tests: {state: ProductSelectionState; action: ProductSelectionAction; result: ProductSelectionState}[] = [
    {
        state: {},
        action: {
            type: ProductSelectionActions.INITIALIZE,
            state: {
                a: {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            },
        },
        result: {
            a: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            },
        },
    },
    {
        state: {},
        action: {
            type: ProductSelectionActions.ADD_CRITERION,
            id: 'a',
            state: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            },
        },
        result: {
            a: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            },
        },
    },
    {
        state: {
            a: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            },
        },
        action: {
            type: ProductSelectionActions.UPDATE_CRITERION,
            id: 'a',
            state: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: false,
            },
        },
        result: {
            a: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: false,
            },
        },
    },
    {
        state: {
            a: {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: true,
            },
        },
        action: {
            type: ProductSelectionActions.REMOVE_CRITERION,
            id: 'a',
        },
        result: {},
    },
];

test.each(tests)('it updates the state using an action #%#', ({state, action, result}) => {
    expect(ProductSelectionReducer(state, action)).toEqual(result);
});
