jest.unmock('./useCategoryTreeRoots');
jest.unmock('./useCategories');

import fetchMock from 'jest-fetch-mock';
import {renderHook} from '@testing-library/react-hooks';
import {useCategoryTreeRoots} from './useCategoryTreeRoots';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

test('It fetches category tree roots', async () => {
    const treeRoots = [
        {
            code: 'catA',
            label: '[catA]',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: '[catB]',
            isLeaf: false,
        },
    ];
    fetchMock.mockResponseOnce(JSON.stringify(treeRoots));

    const {result, waitForNextUpdate} = renderHook(() => useCategoryTreeRoots(), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: treeRoots,
        error: null,
    });
});
