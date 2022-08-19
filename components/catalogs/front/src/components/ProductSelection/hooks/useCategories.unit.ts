jest.unmock('./useCategories');

import fetchMock from 'jest-fetch-mock';
import {renderHook} from '@testing-library/react-hooks';
import {useCategories} from './useCategories';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

test('It fetches categories by code', async () => {
    const categories = [
        {
            code: 'catA',
            label: '[catA]',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: '[catB]',
            isLeaf: true,
        },
    ];
    fetchMock.mockResponseOnce(JSON.stringify(categories));

    const {result, waitForNextUpdate} = renderHook(() => useCategories({codes: ['catA', 'catB']}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/categories?codes=catA%2CcatB&is_root=0&locale=en_US',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: categories,
        error: null,
    });
});

test('It fetches root categories', async () => {
    const categories = [
        {
            code: 'catA',
            label: '[catA]',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: '[catB]',
            isLeaf: true,
        },
    ];
    fetchMock.mockResponseOnce(JSON.stringify(categories));

    const {result, waitForNextUpdate} = renderHook(() => useCategories({isRoot: true}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/categories?codes=&is_root=1&locale=en_US',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: categories,
        error: null,
    });
});

test('It throws an error when codes and isRoot are both used', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useCategories({codes: ['catA'], isRoot: true}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).not.toHaveBeenCalled();
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: true,
        data: undefined,
        error: new Error('Cannot use codes and root simultaneously to fetch categories'),
    });
});

test('It returns an empty array when code list is empty', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useCategories({codes: []}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).not.toHaveBeenCalled();
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    });
});
