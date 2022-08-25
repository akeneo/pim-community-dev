import {CatalogFormErrors} from '../models/CatalogFormErrors';

jest.unmock('./findFirstErrorWithFields');

import {findFirstErrorWithFields} from './findFirstErrorWithFields';

const errors: CatalogFormErrors = [
    {
        message: 'This value must not be empty.',
        propertyPath: '[product_selection_criteria][0][value]',
    },
    {
        message: 'This value must not be empty.',
        propertyPath: '[product_selection_criteria][1][value][amount]',
    },
    {
        message: 'The unit must not be empty.',
        propertyPath: '[product_selection_criteria][2][value][unit]',
    },
];

const tests: [string, string | undefined][] = [
    ['[product_selection_criteria][0][value]', 'This value must not be empty.'],
    ['[product_selection_criteria][1][value]', 'This value must not be empty.'],
    ['[product_selection_criteria][2][value]', 'The unit must not be empty.'],
    ['[product_selection_criteria][3][value]', undefined],
    ['[product_selection_criteria][0][operator]', undefined],
];

test.each(tests)('it returns an error message from "%s"', (input, output) => {
    const result = findFirstErrorWithFields(errors, input);
    expect(result).toEqual(output);
});
