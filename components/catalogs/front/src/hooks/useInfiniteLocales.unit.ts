jest.unmock('./useInfiniteLocales');

import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';

import {act, renderHook} from '@testing-library/react-hooks';
import {useInfiniteLocales} from './useInfiniteLocales';
import fetchMock from 'jest-fetch-mock';

const localeUS = {code: 'en_US', label: 'English'};
const localeFR = {code: 'fr_FR', label: 'French'};
const localeDE = {code: 'de_DE', label: 'German'};

test('it fetches & paginates locales', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([localeUS, localeFR]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteLocales({limit: 2}), {wrapper: ReactQueryWrapper});

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/locales?page=1&limit=2', expect.any(Object));
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
        data: [localeUS, localeFR],
        error: null,
        hasNextPage: true,
        fetchNextPage: expect.any(Function),
    });

    fetchMock.mockResponseOnce(JSON.stringify([localeDE]));

    await act(async () => {
        await result.current.fetchNextPage();
    });

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [localeUS, localeFR, localeDE],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it fetches with default parameters', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([localeUS, localeFR]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteLocales(), {wrapper: ReactQueryWrapper});

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/locales?page=1&limit=20', expect.any(Object));
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
        data: [localeUS, localeFR],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it stops fetching if there is no more pages', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteLocales(), {
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
