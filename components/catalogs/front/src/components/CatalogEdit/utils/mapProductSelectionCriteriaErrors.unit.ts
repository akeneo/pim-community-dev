jest.unmock('./mapProductSelectionCriteriaErrors');
jest.unmock('./findFirstError');

import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {mapProductSelectionCriteriaErrors} from './mapProductSelectionCriteriaErrors';

test('it maps API errors to ProductSelection errors indexed by key', () => {
    const errors: CatalogFormErrors = [
        {
            propertyPath: 'productSelectionCriteria[0][value]',
            message: 'Invalid.',
        },
        {
            propertyPath: 'productSelectionCriteria[3][operator]',
            message: 'Operator invalid.',
        },
        {
            propertyPath: 'productSelectionCriteria[2][value][unit]',
            message: 'Unit invalid.',
        },
    ];
    const keys = ['a', 'b', 'c', 'd'];
    expect(mapProductSelectionCriteriaErrors(errors, keys)).toEqual({
        a: {
            field: undefined,
            operator: undefined,
            value: 'Invalid.',
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
        d: {
            field: undefined,
            operator: 'Operator invalid.',
            value: undefined,
            scope: undefined,
            locale: undefined,
        },
    });
});
