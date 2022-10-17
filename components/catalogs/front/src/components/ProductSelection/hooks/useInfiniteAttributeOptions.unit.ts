import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../../tests/mockFetchResponses';
import {act, renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useInfiniteAttributeOptions} from './useInfiniteAttributeOptions';

jest.unmock('./useInfiniteAttributeOptions');

const XS = {code: 'xs', label: 'XS'};
const S = {code: 's', label: 'S'};
const M = {code: 'm', label: 'M'};

test('it fetches & paginates attribute options', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=&search=&page=1&limit=2',
            json: [XS, S],
        },
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=&search=&page=2&limit=2',
            json: [M],
        },
    ]);

    const {result, waitForNextUpdate} = renderHook(
        () =>
            useInfiniteAttributeOptions({
                attribute: 'clothing_size',
                limit: 2,
            }),
        {
            wrapper: ReactQueryWrapper,
        }
    );

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
        data: [XS, S],
        error: null,
        hasNextPage: true,
        fetchNextPage: expect.any(Function),
    });

    await act(async () => await result.current.fetchNextPage());

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [XS, S, M],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it filters by codes', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=m&search=&page=1&limit=20',
            json: [M],
        },
    ]);

    const {result, waitForNextUpdate} = renderHook(
        () =>
            useInfiniteAttributeOptions({
                attribute: 'clothing_size',
                locale: 'en_US',
                codes: [M.code],
            }),
        {
            wrapper: ReactQueryWrapper,
        }
    );

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [M],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it searches with a string', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=&search=M&page=1&limit=20',
            json: [M],
        },
    ]);

    const {result, waitForNextUpdate} = renderHook(
        () =>
            useInfiniteAttributeOptions({
                attribute: 'clothing_size',
                locale: 'en_US',
                search: 'M',
            }),
        {
            wrapper: ReactQueryWrapper,
        }
    );

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [M],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it stops fetching if there is no more pages', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=&search=&page=1&limit=20',
            json: [],
        },
    ]);

    const {result, waitForNextUpdate} = renderHook(
        () =>
            useInfiniteAttributeOptions({
                attribute: 'clothing_size',
                locale: 'en_US',
            }),
        {
            wrapper: ReactQueryWrapper,
        }
    );

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledTimes(1);
    expect(result.current).toMatchObject({
        hasNextPage: false,
    });

    await result.current.fetchNextPage();

    expect(fetchMock).toHaveBeenCalledTimes(1);
});

test('it can be disabled', async () => {
    const {result} = renderHook(
        () =>
            useInfiniteAttributeOptions({
                attribute: 'clothing_size',
                locale: 'en_US',
                enabled: false,
            }),
        {wrapper: ReactQueryWrapper}
    );

    await result.current.fetchNextPage();

    expect(fetchMock).toHaveBeenCalledTimes(0);
});
