jest.unmock('./indexify');

import {indexify} from './indexify';

test('it returns an array as object with random keys while preserving the order', () => {
    const result = indexify(['A', 'B', 'C']);
    expect(Object.values(result)).toEqual(['A', 'B', 'C']);
});
