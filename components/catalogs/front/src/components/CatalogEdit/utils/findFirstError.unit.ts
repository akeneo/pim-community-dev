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

test('it returns undefined if there is not matches', () => {
    const errors: CatalogFormErrors = [];

    expect(findFirstError(errors, 'bar')).toBeUndefined();
});

const pathErrors: CatalogFormErrors = [
    {
        message: 'This value must not be empty.',
        propertyPath: 'productSelectionCriteria[0][value]',
    },
    {
        message: 'This value must not be empty.',
        propertyPath: 'productSelectionCriteria[1][value][amount]',
    },
    {
        message: 'The unit must not be empty.',
        propertyPath: 'productSelectionCriteria[2][value][unit]',
    },
];

const pathTests: [string, string | undefined][] = [
    ['productSelectionCriteria[0][value]', 'This value must not be empty.'],
    ['productSelectionCriteria[1][value]', 'This value must not be empty.'],
    ['productSelectionCriteria[2][value]', 'The unit must not be empty.'],
    ['productSelectionCriteria[3][value]', undefined],
    ['productSelectionCriteria[0][operator]', undefined],
];

test.each(pathTests)('it returns an error message from "%s"', (input, output) => {
    const result = findFirstError(pathErrors, input);
    expect(result).toEqual(output);
});
