jest.unmock('./getTabsValidationStatus');

import {Tabs} from '../components/TabBar';
import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {getTabsValidationStatus} from './getTabsValidationStatus';

const tests: {errors: CatalogFormErrors; result: {[key in Tabs]: boolean}}[] = [
    {
        errors: [],
        result: {
            [Tabs.PRODUCT_SELECTION]: false,
            [Tabs.PRODUCT_VALUE_FILTERS]: false,
            [Tabs.PRODUCT_MAPPING]: false,
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
            [Tabs.PRODUCT_SELECTION]: false,
            [Tabs.PRODUCT_VALUE_FILTERS]: false,
            [Tabs.PRODUCT_MAPPING]: false,
        },
    },
    {
        errors: [
            {
                propertyPath: 'productSelectionCriteria[0][value]',
                message: 'Invalid.',
            },
        ],
        result: {
            [Tabs.PRODUCT_SELECTION]: true,
            [Tabs.PRODUCT_VALUE_FILTERS]: false,
            [Tabs.PRODUCT_MAPPING]: false,
        },
    },
    {
        errors: [
            {
                propertyPath: 'productValueFilters[0][value]',
                message: 'Invalid.',
            },
        ],
        result: {
            [Tabs.PRODUCT_SELECTION]: false,
            [Tabs.PRODUCT_VALUE_FILTERS]: true,
            [Tabs.PRODUCT_MAPPING]: false,
        },
    },
    {
        errors: [
            {
                propertyPath: 'productMapping[0][value]',
                message: 'Invalid.',
            },
        ],
        result: {
            [Tabs.PRODUCT_SELECTION]: false,
            [Tabs.PRODUCT_VALUE_FILTERS]: false,
            [Tabs.PRODUCT_MAPPING]: true,
        },
    },
];

test.each(tests)('it returns either a tab has an error or not #%#', ({errors, result}) => {
    expect(getTabsValidationStatus(errors)).toEqual(result);
});
