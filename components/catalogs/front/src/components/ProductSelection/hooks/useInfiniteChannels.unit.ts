jest.unmock('./useInfiniteChannels');

import {act, renderHook} from '@testing-library/react-hooks';
import {useInfiniteChannels} from './useInfiniteChannels';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';

const localeUS = {code: 'en_US', label: 'English'};
const localeFR = {code: 'fr_FR', label: 'French'};
const localeDE = {code: 'de_DE', label: 'German'};
const localeES = {code: 'es_ES', label: 'Spanish'};
const channelPrint = {code: 'print', label: 'Print', locales: [localeUS, localeFR, localeDE]};
const channelEcommerce = {code: 'ecommerce', label: 'E-commerce', locales: [localeUS, localeFR, localeES]};
const channelMobile = {code: 'mobile', label: 'Mobile', locales: [localeFR]};

test('it fetches & paginates channels', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([channelPrint, channelEcommerce]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteChannels({limit: 2}), {wrapper: ReactQueryWrapper});

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels?page=1&limit=2&code=', expect.any(Object));
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

test('it filters channel with a code', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([channelPrint]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteChannels({code: 'print'}), {
        wrapper: ReactQueryWrapper,
    });

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels?page=1&limit=20&code=print', expect.any(Object));
    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [channelPrint],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [channelPrint],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });

    await act(async () => {
        await result.current.fetchNextPage();
    });

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [channelPrint],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it fetches with default parameters', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([channelPrint, channelEcommerce]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteChannels(), {wrapper: ReactQueryWrapper});

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels?page=1&limit=20&code=', expect.any(Object));
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
