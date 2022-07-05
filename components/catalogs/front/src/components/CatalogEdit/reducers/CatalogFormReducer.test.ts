jest.unmock('./CatalogFormReducer');

import {CatalogFormValues} from '../models/CatalogFormValues';
import {CatalogFormAction, CatalogFormActions, CatalogFormReducer} from './CatalogFormReducer';
import {Operator} from '../../ProductSelection/models/Operator';

const tests: {state: CatalogFormValues; action: CatalogFormAction; result: CatalogFormValues}[] = [
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
        },
        action: {
            type: CatalogFormActions.INITIALIZE,
            state: {
                enabled: true,
                product_selection_criteria: {},
            },
        },
        result: {
            enabled: true,
            product_selection_criteria: {},
        },
    },
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
        },
        action: {
            type: CatalogFormActions.SET_ENABLED,
            value: true,
        },
        result: {
            enabled: true,
            product_selection_criteria: {},
        },
    },
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
        },
        action: {
            type: CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA,
            value: {
                a: {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            },
        },
        result: {
            enabled: false,
            product_selection_criteria: {
                a: {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            },
        },
    },
];

test.each(tests)('it updates the state using an action #%#', ({state, action, result}) => {
    expect(CatalogFormReducer(state, action)).toEqual(result);
});
