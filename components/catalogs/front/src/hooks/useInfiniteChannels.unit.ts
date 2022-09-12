jest.unmock('./useInfiniteChannels');

import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';
import {act, renderHook} from '@testing-library/react-hooks';
import {useInfiniteChannels} from './useInfiniteChannels';
import fetchMock from 'jest-fetch-mock';

const channelPrint = {code: 'print', label: 'Print'};
const channelEcommerce = {code: 'ecommerce', label: 'E-commerce'};
const channelMobile = {code: 'mobile', label: 'Mobile'};

test('it fetches & paginates channels', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([channelPrint, channelEcommerce]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteChannels({limit: 2}), {wrapper: ReactQueryWrapper});

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels?page=1&limit=2', expect.any(Object));
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
        data: [channelPrint, channelEcommerce],
        error: null,
        hasNextPage: true,
        fetchNextPage: expect.any(Function),
    });

    fetchMock.mockResponseOnce(JSON.stringify([channelMobile]));

    await act(async () => {
        await result.current.fetchNextPage();
    });

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [channelPrint, channelEcommerce, channelMobile],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it fetches with default parameters', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([channelPrint, channelEcommerce]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteChannels(), {wrapper: ReactQueryWrapper});

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels?page=1&limit=20', expect.any(Object));
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
        data: [channelPrint, channelEcommerce],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it stops fetching if there is no more pages', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteChannels(), {
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
