jest.unmock('./generateRandomId');

import {generateRandomId} from './generateRandomId';

test('it returns a random id as string', () => {
    const result = generateRandomId();
    expect(result).toEqual(expect.any(String));
});
