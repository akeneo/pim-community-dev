jest.unmock('./useScopedLocales');

import {renderHook} from '@testing-library/react-hooks';
import {mocked} from 'ts-jest/utils';
import {useScopedLocales} from './useScopedLocales';
import {useInfiniteChannels} from './useInfiniteChannels';

const locales = [
    {code: 'en_US', label: 'English'},
    {code: 'fr_FR', label: 'French'},
    {code: 'de_DE', label: 'German'},
];

const channels = [{code: 'print', label: 'Print', locales: locales}];

test('it returns list of locales for a given channel', () => {
    mocked(useInfiniteChannels).mockReturnValueOnce({
        isLoading: false,
        isError: false,
        data: channels,
        error: null,
        hasNextPage: false,
        fetchNextPage: jest.fn(),
    });

    const {result} = renderHook(() => useScopedLocales('print'));

    expect(result.current).toStrictEqual(locales);
});

test('it returns an empty list of locales for a unknown channel', () => {
    mocked(useInfiniteChannels).mockReturnValueOnce({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
        hasNextPage: false,
        fetchNextPage: jest.fn(),
    });

    const {result} = renderHook(() => useScopedLocales('print'));

    expect(result.current).toStrictEqual([]);
});

test('it returns an empty list of locales when specified channel is null', () => {
    mocked(useInfiniteChannels).mockReturnValueOnce({
        isLoading: false,
        isError: false,
        data: channels,
        error: null,
        hasNextPage: false,
        fetchNextPage: jest.fn(),
    });

    const {result} = renderHook(() => useScopedLocales(null));

    expect(result.current).toStrictEqual([]);
});

test('it returns an empty list of locales when channels are loading', () => {
    mocked(useInfiniteChannels).mockReturnValueOnce({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
        hasNextPage: false,
        fetchNextPage: jest.fn(),
    });

    const {result} = renderHook(() => useScopedLocales('print'));

    expect(result.current).toStrictEqual([]);
});

test('it throws an error', () => {
    mocked(useInfiniteChannels).mockReturnValueOnce({
        isLoading: false,
        isError: true,
        data: undefined,
        error: 'Error occurred',
        hasNextPage: false,
        fetchNextPage: jest.fn(),
    });

    const {result} = renderHook(() => useScopedLocales('print'));

    expect(result.error.message).toBe('Error occurred');
});

test('it returns an empty list of locales when data is undefined', () => {
    mocked(useInfiniteChannels).mockReturnValueOnce({
        isLoading: false,
        isError: false,
        data: undefined,
        error: null,
        hasNextPage: false,
        fetchNextPage: jest.fn(),
    });

    const {result} = renderHook(() => useScopedLocales('print'));

    expect(result.current).toStrictEqual([]);
});
