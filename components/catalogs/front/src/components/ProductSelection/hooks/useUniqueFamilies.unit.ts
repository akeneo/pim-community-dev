jest.unmock('./useUniqueFamilies');

import {renderHook} from '@testing-library/react-hooks';
import {useUniqueFamilies} from './useUniqueFamilies';
import {Family} from '../models/Family';

const foo: Family = {
    code: 'foo',
    label: 'Foo',
};

const bar: Family = {
    code: 'bar',
    label: 'Bar',
};

const tests: {selection: Family[] | undefined; results: Family[] | undefined; expected: Family[]}[] = [
    {
        selection: [foo],
        results: [foo, bar],
        expected: [foo, bar],
    },
    {
        selection: undefined,
        results: [foo, bar],
        expected: [foo, bar],
    },
    {
        selection: [foo],
        results: undefined,
        expected: [foo],
    },
    {
        selection: undefined,
        results: undefined,
        expected: [],
    },
];

test.each(tests)('it updates the state using an action #%#', ({selection, results, expected}) => {
    const {result} = renderHook(() => useUniqueFamilies(selection, results));

    expect(result.current).toEqual(expected);
});
