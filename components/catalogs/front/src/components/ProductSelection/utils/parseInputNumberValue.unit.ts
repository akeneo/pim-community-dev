jest.unmock('./parseInputNumberValue');

import {parseInputNumberValue} from './parseInputNumberValue';

const tests: [string, string][] = [
    ['42', '42'],
    ['42.42', '42.42'],
    ['42...', '42.'],
    ['42.420', '42.420'],
    ['42.42.42', '42.42'],
    ['a', ''],
    ['.', ''],
    ['42.', '42.'],
];

test.each(tests)('it returns the number from "%s"', (input, output) => {
    const result = parseInputNumberValue(input);
    const expected = Number(output).toString() === output ? Number(output) : output;

    expect(result).toEqual(expected);
});

test('it returns typed as a string when Number() would change it', () => {
    expect(typeof parseInputNumberValue('42.')).toEqual('string');
});

test('it returns typed as a number when Number() would not change it', () => {
    expect(typeof parseInputNumberValue('42')).toEqual('number');
});
