jest.unmock('./useCategoryChildren');

import fetchMock from 'jest-fetch-mock';

import {renderHook} from '@testing-library/react-hooks';
import {useCategoryChildren} from './useCategoryChildren';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

test('It fetches the API response', async () => {
    const childrenCategories = [
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
    fetchMock.mockResponseOnce(JSON.stringify(childrenCategories));

    const {result, waitForNextUpdate} = renderHook(() => useCategoryChildren('parent_code'), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/categories/parent_code/children', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: childrenCategories,
        error: null,
    });
});
