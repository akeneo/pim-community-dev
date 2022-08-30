jest.unmock('./useCategoriesByCodes');
jest.unmock('./useCategories');

import {renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';
import {useCategoriesByCodes} from './useCategoriesByCodes';

beforeEach(() => {
    fetchMock.mockResponse(req => {
        switch (req.url) {
            // useCategories: catA, catB
            case '/rest/catalogs/categories?codes=catA%2CcatB&is_root=0&locale=en_US':
                return Promise.resolve(
                    JSON.stringify([
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
                    ])
                );
            default:
                throw Error(req.url);
        }
    });
});

test('it fetches categories', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useCategoriesByCodes(['catA', 'catB']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        error: null,
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
    });

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        error: null,
        data: [
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
        ],
    });
});
