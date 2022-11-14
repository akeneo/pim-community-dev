jest.unmock('./CatalogFormReducer');

import {CatalogFormValues} from '../models/CatalogFormValues';
import {CatalogFormAction, CatalogFormActions, CatalogFormReducer} from './CatalogFormReducer';
import {Operator} from '../../ProductSelection';

const tests: {state: CatalogFormValues; action: CatalogFormAction; result: CatalogFormValues}[] = [
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
        },
        action: {
            type: CatalogFormActions.INITIALIZE,
            state: {
                enabled: true,
                product_selection_criteria: {},
                product_value_filters: {},
                product_mapping: {},
            },
        },
        result: {
            enabled: true,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
        },
    },
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
        },
        action: {
            type: CatalogFormActions.SET_ENABLED,
            value: true,
        },
        result: {
            enabled: true,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
        },
    },
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
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
            product_value_filters: {},
            product_mapping: {},
        },
    },
    {
        state: {
            enabled: false,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
        },
        action: {
            type: CatalogFormActions.SET_PRODUCT_VALUE_FILTERS,
            value: {
                channels: ['print', 'ecommerce'],
                locales: ['en_US', 'fr_FR'],
                currencies: ['USD', 'EUR'],
            },
        },
        result: {
            enabled: false,
            product_selection_criteria: {},
            product_value_filters: {
                channels: ['print', 'ecommerce'],
                locales: ['en_US', 'fr_FR'],
                currencies: ['USD', 'EUR'],
            },
            product_mapping: {},
        },
    },

    {
        state: {
            enabled: false,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {},
        },
        action: {
            type: CatalogFormActions.SET_PRODUCT_MAPPING,
            value: {
                uuid: {
                    source: 'uuid',
                    locale: null,
                    scope: null,
                },
                name: {
                    source: 'title',
                    locale: 'en_US',
                    scope: 'ecommerce',
                },
            },
        },
        result: {
            enabled: false,
            product_selection_criteria: {},
            product_value_filters: {},
            product_mapping: {
                uuid: {
                    source: 'uuid',
                    locale: null,
                    scope: null,
                },
                name: {
                    source: 'title',
                    locale: 'en_US',
                    scope: 'ecommerce',
                },
            },
        },
    },
];

test.each(tests)('it updates the state using an action #%#', ({state, action, result}) => {
    expect(CatalogFormReducer(state, action)).toEqual(result);
});
