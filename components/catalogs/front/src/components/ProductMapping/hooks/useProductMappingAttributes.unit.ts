import {useProductMappingAttributes} from './useProductMappingAttributes';

jest.unmock('./useProductMappingAttributes');

import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify({
            name: {
                code: 'name',
                label: 'Name',
            },
            description: {
                code: 'description',
                label: 'Description',
            },
        })
    );

    const {result, waitForNextUpdate} = renderHook(
        () => useProductMappingAttributes('123e4567-e89b-12d3-a456-426614174000'),
        {
            wrapper: ReactQueryWrapper,
        }
    );

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/123e4567-e89b-12d3-a456-426614174000/mapping/product/attributes',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: {
            name: {
                code: 'name',
                label: 'Name',
            },
            description: {
                code: 'description',
                label: 'Description',
            },
        },
        error: null,
    });
});
