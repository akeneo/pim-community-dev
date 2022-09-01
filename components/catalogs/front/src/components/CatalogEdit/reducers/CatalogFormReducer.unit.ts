jest.unmock('./CatalogFormReducer');

import {CatalogFormValues} from '../models/CatalogFormValues';
import {CatalogFormAction, CatalogFormActions, CatalogFormReducer} from './CatalogFormReducer';
import {Operator} from '../../ProductSelection';

const tests: {state: CatalogFormValues; action: CatalogFormAction; result: CatalogFormValues}[] = [
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
            filter_values_criteria: {channel: []},
        },
        action: {
            type: CatalogFormActions.INITIALIZE,
            state: {
                enabled: true,
                product_selection_criteria: {},
                filter_values_criteria: {channel: []},
            },
        },
        result: {
            enabled: true,
            product_selection_criteria: {},
            filter_values_criteria: {channel: []},
        },
    },
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
            filter_values_criteria: {channel: []},
        },
        action: {
            type: CatalogFormActions.SET_ENABLED,
            value: true,
        },
        result: {
            enabled: true,
            product_selection_criteria: {},
            filter_values_criteria: {channel: []},
        },
    },
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
            filter_values_criteria: {channel: []},
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
            filter_values_criteria: {channel: []},
        },
    },
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
            filter_values_criteria: {channel: []},
        },
        action: {
            type: CatalogFormActions.SET_FILTER_VALUES_CRITERIA,
            value: {channel: ['print', 'ecommerce']},
        },
        result: {
            enabled: false,
            product_selection_criteria: {},
            filter_values_criteria: {channel: ['print', 'ecommerce']},
        },
    },
];

test.each(tests)('it updates the state using an action #%#', ({state, action, result}) => {
    expect(CatalogFormReducer(state, action)).toEqual(result);
});
