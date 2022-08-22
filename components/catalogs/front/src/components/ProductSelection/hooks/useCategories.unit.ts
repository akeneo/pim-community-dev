jest.unmock('./useCategories');

import fetchMock from 'jest-fetch-mock';
import {renderHook} from '@testing-library/react-hooks';
import {useCategories} from './useCategories';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

beforeEach(() => {
    fetchMock.mockResponse(req => {
        switch (req.url) {
            // fetch: catA, catB
            case '/rest/catalogs/categories?codes=catA%2CcatB&is_root=0&locale=en_US':
                return Promise.resolve(
                    JSON.stringify([
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
                    ])
                );
            // fetch root categories
            case '/rest/catalogs/categories?codes=&is_root=1&locale=en_US':
                return Promise.resolve(
                    JSON.stringify([
                        {
                            code: 'master',
                            label: '[master]',
                            isLeaf: false,
                        },
                        {
                            code: 'print',
                            label: '[print]',
                            isLeaf: false,
                        },
                    ])
                );
            default:
                throw Error(req.url);
        }
    });
});

test('It fetches categories by code', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useCategories({codes: ['catA', 'catB']}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        error: null,
        data: undefined,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/categories?codes=catA%2CcatB&is_root=0&locale=en_US',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        error: null,
        data: [
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
        ],
    });
});

test('It fetches root categories', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useCategories({isRoot: true}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        error: null,
        data: undefined,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/categories?codes=&is_root=1&locale=en_US',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        error: null,
        data: [
            {
                code: 'master',
                label: '[master]',
                isLeaf: false,
            },
            {
                code: 'print',
                label: '[print]',
                isLeaf: false,
            },
        ],
    });
});

test('It throws an error when codes and isRoot are both used', async () => {
    // mute the error in the output
    jest.spyOn(console, 'error');
    /* eslint-disable-next-line no-console */
    (console.error as jest.Mock).mockImplementation(() => null);

    const {result, waitForValueToChange} = renderHook(() => useCategories({codes: ['catA'], isRoot: true}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForValueToChange(() => result.current.isError);

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
