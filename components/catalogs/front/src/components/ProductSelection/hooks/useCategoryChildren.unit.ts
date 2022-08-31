jest.unmock('./useCategoryChildren');

import fetchMock from 'jest-fetch-mock';

import {renderHook} from '@testing-library/react-hooks';
import {useCategoryChildren} from './useCategoryChildren';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

beforeEach(() => {
    fetchMock.mockResponse(req => {
        switch (req.url) {
            // fetch children of parent_code
            case '/rest/catalogs/categories/parent_code/children?locale=en_US':
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
            default:
                throw Error(req.url);
        }
    });
});

test('It fetches the API response', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useCategoryChildren('parent_code'), {
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
        '/rest/catalogs/categories/parent_code/children?locale=en_US',
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
