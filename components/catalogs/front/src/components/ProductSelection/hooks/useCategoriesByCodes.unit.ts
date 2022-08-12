jest.unmock('./useCategoriesByCodes');
jest.unmock('./useCategories');

import {renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';
import {useCategoriesByCodes} from './useCategoriesByCodes';

test('it fetches categories', async () => {
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

    fetchMock.mockResponses(
        //call with catA and catB
        JSON.stringify(categories),
        //call with empty array
        JSON.stringify([])
    );

    const {result, waitForNextUpdate} = renderHook(() => useCategoriesByCodes(['catA', 'catB']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: [],
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
