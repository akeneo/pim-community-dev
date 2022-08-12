jest.unmock('./useCategoryChildren');

import fetchMock from 'jest-fetch-mock';

import {renderHook} from '@testing-library/react-hooks';
import {useCategoryChildren} from './useCategoryChildren';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

test('It fetches the API response', async () => {
    const childrenCategories = [
        {
            id: 1,
            code: 'catA',
            label: '[catA]',
            isLeaf: false,
        },
        {
            id: 43,
            code: 'catB',
            label: '[catB]',
            isLeaf: true,
        },
    ];
    fetchMock.mockResponseOnce(JSON.stringify(childrenCategories));

    const {result, waitForNextUpdate} = renderHook(() => useCategoryChildren(5), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/categories/5/children', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: childrenCategories,
        error: null,
    });
});
