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
            [Tabs.FILTER_VALUES]: false,
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
            [Tabs.FILTER_VALUES]: false,
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
            [Tabs.FILTER_VALUES]: false,
        },
    },
    {
        errors: [
            {
                propertyPath: '[filter_values][0][value]',
                message: 'Invalid.',
            },
        ],
        result: {
            [Tabs.SETTINGS]: false,
            [Tabs.PRODUCT_SELECTION]: false,
            [Tabs.FILTER_VALUES]: true,
        },
    },
];

test.each(tests)('it returns either a tab has an error or not #%#', ({errors, result}) => {
    expect(getTabsValidationStatus(errors)).toEqual(result);
});
