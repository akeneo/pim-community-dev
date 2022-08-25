jest.unmock('./mapProductSelectionCriteriaErrors');
jest.unmock('./findFirstError');
jest.unmock('./findFirstErrorWithFields');

import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {mapProductSelectionCriteriaErrors} from './mapProductSelectionCriteriaErrors';

test('it maps API errors to ProductSelection errors indexed by key', () => {
    const errors: CatalogFormErrors = [
        {
            propertyPath: '[product_selection_criteria][0][operator]',
            message: 'Operator invalid.',
        },
        {
            propertyPath: '[product_selection_criteria][2][value][unit]',
            message: 'Unit invalid.',
        },
    ];
    const keys = ['a', 'b', 'c'];
    expect(mapProductSelectionCriteriaErrors(errors, keys)).toEqual({
        a: {
            field: undefined,
            operator: 'Operator invalid.',
            value: undefined,
            scope: undefined,
            locale: undefined,
        },
        b: {
            field: undefined,
            operator: undefined,
            value: undefined,
            scope: undefined,
            locale: undefined,
        },
        c: {
            field: undefined,
            operator: undefined,
            value: 'Unit invalid.',
            scope: undefined,
            locale: undefined,
        },
    });
});
