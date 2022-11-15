jest.unmock('./useProductMappingSchema');

import fetchMock from 'jest-fetch-mock';
import {renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';
import {useProductMappingSchema} from './useProductMappingSchema';

test('it returns a product mapping schema', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify({
            $id: 'https://example.com/product',
            $schema: 'https://api.akeneo.com/mapping/product/0.0.1/schema',
            $comment: 'My first schema !',
            title: 'Product Mapping',
            description: 'JSON Schema describing the structure of products expected by our application',
            type: 'object',
            properties: {
                uuid: {
                    type: 'string',
                },
                name: {
                    type: 'string',
                },
            },
        }),
        {
            status: 200,
        }
    );

    const {result, waitForNextUpdate} = renderHook(
        () => useProductMappingSchema('123e4567-e89b-12d3-a456-426614174000'),
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
        '/rest/catalogs/123e4567-e89b-12d3-a456-426614174000/mapping-schemas/product',
        expect.any(Object)
    );
    expect(result.current.data).toEqual({
        $id: 'https://example.com/product',
        $schema: 'https://api.akeneo.com/mapping/product/0.0.1/schema',
        $comment: 'My first schema !',
        title: 'Product Mapping',
        description: 'JSON Schema describing the structure of products expected by our application',
        type: 'object',
        properties: {
            uuid: {
                type: 'string',
            },
            name: {
                type: 'string',
            },
        },
    });
});
