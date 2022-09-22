jest.unmock('./useCatalogErrors');

import fetchMock from 'jest-fetch-mock';
import {renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useCatalogErrors} from './useCatalogErrors';

test('it returns validation errors', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify({
            message: 'Invalid.',
            errors: [
                {
                    propertyPath: '[enabled]',
                    message: 'Invalid.',
                },
            ],
        }),
        {
            status: 200,
        }
    );

    const {result, waitForNextUpdate} = renderHook(() => useCatalogErrors('123e4567-e89b-12d3-a456-426614174000'), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/123e4567-e89b-12d3-a456-426614174000/errors',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: {
            message: 'Invalid.',
            errors: [
                {
                    propertyPath: '[enabled]',
                    message: 'Invalid.',
                },
            ],
        },
        error: null,
    });
});
