jest.unmock('./useUniqueEntitiesByCode');

import {renderHook} from '@testing-library/react-hooks';
import {useUniqueEntitiesByCode} from './useUniqueEntitiesByCode';

type Entity = {
    code: string;
    label: string;
};

const foo: Entity = {
    code: 'foo',
    label: 'Foo',
};

const bar: Entity = {
    code: 'bar',
    label: 'Bar',
};

const tests: {first: Entity[] | undefined; second: Entity[] | undefined; expected: Entity[]}[] = [
    {
        first: [foo],
        second: [foo, bar],
        expected: [foo, bar],
    },
    {
        first: undefined,
        second: [foo, bar],
        expected: [foo, bar],
    },
    {
        first: [foo],
        second: undefined,
        expected: [foo],
    },
    {
        first: [foo],
        second: [bar],
        expected: [foo, bar],
    },
    {
        first: undefined,
        second: undefined,
        expected: [],
    },
];

test.each(tests)('It return an array without duplicates #%#', ({first, second, expected}) => {
    const {result} = renderHook(() => useUniqueEntitiesByCode(first, second));

    expect(result.current).toEqual(expected);
});
