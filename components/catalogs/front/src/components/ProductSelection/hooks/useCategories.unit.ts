jest.unmock('./useCategories');

import fetchMock from 'jest-fetch-mock';
import {renderHook} from '@testing-library/react-hooks';
import {useCategories} from './useCategories';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

test('It fetches the API response', async () => {
    const categories = [
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
    fetchMock.mockResponseOnce(JSON.stringify(categories));

    const {result, waitForNextUpdate} = renderHook(() => useCategories(['catA', 'catB']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/categories?codes=catA,catB', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: categories,
        error: null,
    });
});
