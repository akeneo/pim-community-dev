jest.unmock('./useCategoriesByCodes');
jest.unmock('./useCategories');

import {renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';
import {useCategoriesByCodes} from './useCategoriesByCodes';

test('it fetches categories', async () => {
    const categories = [
        {
            code: 'catA',
            label: 'Category A',
            isLeaf: false,
        },
        {
            code: 'catB',
            label: 'Category B',
            isLeaf: true,
        },
    ];

    fetchMock.mockResponses(JSON.stringify(categories));

    const {result, waitForNextUpdate} = renderHook(() => useCategoriesByCodes(['catA', 'catB']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: [
            {
                code: 'catA',
                label: '[catA]',
                isLeaf: true,
            },
            {
                code: 'catB',
                label: '[catB]',
                isLeaf: true,
            },
        ],
        error: null,
    });

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: categories,
        error: null,
    });
});
