jest.unmock('./mapProductValueFiltersErrors');
jest.unmock('./findFirstError');

import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {mapProductValueFiltersErrors} from './mapProductValueFiltersErrors';

test('it returns errors matching the property path of product value filters', () => {
    const errors: CatalogFormErrors = [
        {
            propertyPath: 'productValueFilters[channels][0][value]',
            message: 'This is an error',
        },
        {
            propertyPath: 'productValueFilters[channels][1][value]',
            message: 'This is a second message but it should not happen',
        },
    ];

    expect(mapProductValueFiltersErrors(errors)).toEqual({channels: 'This is an error'});
});

test('it returns undefined if there is not matches', () => {
    const errors: CatalogFormErrors = [
        {
            propertyPath: '[product_criteria_selection][size][0][value]',
            message: 'This is an error',
        },
    ];

    expect(mapProductValueFiltersErrors(errors)).toEqual({channels: undefined});
});
