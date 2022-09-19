jest.unmock('./getTabsValidationStatus');

import {Tabs} from '../components/TabBar';
import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {getTabsValidationStatus} from './getTabsValidationStatus';

const tests: {errors: CatalogFormErrors; result: {[key in Tabs]: boolean}}[] = [
    {
        errors: [],
        result: {
            [Tabs.SETTINGS]: false,
            [Tabs.PRODUCT_SELECTION]: false,
            [Tabs.PRODUCT_VALUE_FILTERS]: false,
        },
    },
    {
        errors: [
            {
                propertyPath: '[enabled]',
                message: 'Invalid.',
            },
        ],
        result: {
            [Tabs.SETTINGS]: true,
            [Tabs.PRODUCT_SELECTION]: false,
            [Tabs.PRODUCT_VALUE_FILTERS]: false,
        },
    },
    {
        errors: [
            {
                propertyPath: '[product_selection_criteria][0][value]',
                message: 'Invalid.',
            },
        ],
        result: {
            [Tabs.SETTINGS]: false,
            [Tabs.PRODUCT_SELECTION]: true,
            [Tabs.PRODUCT_VALUE_FILTERS]: false,
        },
    },
    {
        errors: [
            {
                propertyPath: '[product_value_filters][0][value]',
                message: 'Invalid.',
            },
        ],
        result: {
            [Tabs.SETTINGS]: false,
            [Tabs.PRODUCT_SELECTION]: false,
            [Tabs.PRODUCT_VALUE_FILTERS]: true,
        },
    },
];

test.each(tests)('it returns either a tab has an error or not #%#', ({errors, result}) => {
    expect(getTabsValidationStatus(errors)).toEqual(result);
});
