jest.unmock('./findFirstError');

import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {findFirstError} from './findFirstError';

test('it returns the first error matching the property path', () => {
    const errors: CatalogFormErrors = [
        {
            propertyPath: 'foo',
            message: 'one',
        },
        {
            propertyPath: 'bar',
            message: 'two',
        },
        {
            propertyPath: 'bar',
            message: 'three',
        },
    ];

    expect(findFirstError(errors, 'bar')).toEqual('two');
});

test('it returns null if there is not matches', () => {
    const errors: CatalogFormErrors = [];

    expect(findFirstError(errors, 'bar')).toBeNull();
});
