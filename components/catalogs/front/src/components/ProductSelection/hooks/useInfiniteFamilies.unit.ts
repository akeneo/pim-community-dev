jest.unmock('./useInfiniteFamilies');

import {Family} from '../models/Family';
import {act, renderHook} from '@testing-library/react-hooks';
import {useInfiniteFamilies} from './useInfiniteFamilies';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';

test('it fetch & paginate families', async () => {
    const families: Family[] = [
        {
            code: 'foo',
            label: 'Foo',
        },
        {
            code: 'bar',
            label: 'Bar',
        },
        {
            code: 'foobar',
            label: 'FooBar',
        },
    ];

    fetchMock.mockResponseOnce(JSON.stringify(families.slice(0, 2)));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteFamilies({limit: 2}), {wrapper: ReactQueryWrapper});

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/families?page=1&limit=2&codes=&search=', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: families.slice(0, 2),
        error: null,
        hasNextPage: true,
        fetchNextPage: expect.any(Function),
    });

    fetchMock.mockResponseOnce(JSON.stringify(families.slice(2, 3)));

    await act(async () => {
        await result.current.fetchNextPage();
    });

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: families,
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it filters by codes', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                code: 'foo',
                label: 'Foo',
            },
        ])
    );

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteFamilies({codes: ['foo']}), {
        wrapper: ReactQueryWrapper,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/families?page=1&limit=20&codes=foo&search=',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'foo',
                label: 'Foo',
            },
        ],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it searches with a string', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                code: 'foo',
                label: 'Foo',
            },
        ])
    );

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteFamilies({search: 'foo'}), {
        wrapper: ReactQueryWrapper,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/families?page=1&limit=20&codes=&search=foo',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'foo',
                label: 'Foo',
            },
        ],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it stops fetching if there is no more pages', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteFamilies(), {
        wrapper: ReactQueryWrapper,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledTimes(1);
    expect(result.current).toMatchObject({
        hasNextPage: false,
    });

    await result.current.fetchNextPage();

    expect(fetchMock).toHaveBeenCalledTimes(1);
});

test('it can be disabled', async () => {
    const {result} = renderHook(() => useInfiniteFamilies({enabled: false}), {
        wrapper: ReactQueryWrapper,
    });

    await result.current.fetchNextPage();

    expect(fetchMock).toHaveBeenCalledTimes(0);
});
