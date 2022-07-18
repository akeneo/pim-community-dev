jest.unmock('./mapProductSelectionCriteriaErrors');
jest.unmock('./findFirstError');

import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {mapProductSelectionCriteriaErrors} from './mapProductSelectionCriteriaErrors';

test('it maps API errors to ProductSelection errors indexed by key', () => {
    const errors: CatalogFormErrors = [
        {
            propertyPath: '[product_selection_criteria][0][value]',
            message: 'Invalid.',
        },
    ];
    const keys = ['a', 'b'];
    expect(mapProductSelectionCriteriaErrors(errors, keys)).toEqual({
        a: {
            field: null,
            operator: null,
            value: 'Invalid.',
        },
        b: {
            field: null,
            operator: null,
            value: null,
        },
    });
});
