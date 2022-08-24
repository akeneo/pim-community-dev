jest.unmock('./useCategoryTreeRoots');
jest.unmock('./useCategories');

import fetchMock from 'jest-fetch-mock';
import {renderHook} from '@testing-library/react-hooks';
import {useCategoryTreeRoots} from './useCategoryTreeRoots';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

beforeEach(() => {
    fetchMock.mockResponse(req => {
        switch (req.url) {
            // useCategory with isRoot set to true
            case '/rest/catalogs/categories?codes=&is_root=1&locale=en_US':
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
                            isLeaf: false,
                        },
                    ])
                );
            default:
                throw Error(req.url);
        }
    });
});

test('It fetches category tree roots', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useCategoryTreeRoots(), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        error: null,
        data: undefined,
    });

    await waitForNextUpdate();

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
                isLeaf: false,
            },
        ],
    });
});
