jest.unmock('./useChannelsWithSelectedChannel');

import {renderHook} from '@testing-library/react-hooks';
import {useChannelsWithSelectedChannel} from './useChannelsWithSelectedChannel';
import {Channel} from '../models/Channel';

const foo: Channel = {
    code: 'foo',
    label: 'Foo',
};

const bar: Channel = {
    code: 'bar',
    label: 'Bar',
};

const tests: {selectedChannel: Channel | undefined; results: Channel[] | undefined; expected: Channel[]}[] = [
    {
        selectedChannel: foo,
        results: [foo, bar],
        expected: [foo, bar],
    },
    {
        selectedChannel: foo,
        results: [bar],
        expected: [bar, foo],
    },
    {
        selectedChannel: undefined,
        results: [foo],
        expected: [foo],
    },
    {
        selectedChannel: foo,
        results: undefined,
        expected: [foo],
    },
    {
        selectedChannel: undefined,
        results: undefined,
        expected: [],
    },
];

test.each(tests)('it updates the state using an action #%#', ({selectedChannel, results, expected}) => {
    const {result} = renderHook(() => useChannelsWithSelectedChannel(selectedChannel, results));

    expect(result.current).toEqual(expected);
});
