jest.unmock('./useSaveCatalog');

import fetchMock from 'jest-fetch-mock';
import {act, renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useSaveCatalog} from './useSaveCatalog';

test('it calls the API', async () => {
    const {result} = renderHook(() => useSaveCatalog(), {
        wrapper: ReactQueryWrapper,
    });

    await act(async () => {
        const [success, errors] = await result.current({
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            values: {
                enabled: true,
                product_selection_criteria: [],
            },
        });

        expect(success).toBeTruthy();
        expect(errors).toBeNull();
    });

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/a4ecb5c7-7e80-44a8-baa1-549db0707f79',
        expect.objectContaining({
            method: 'PATCH',
            body: JSON.stringify({
                enabled: true,
                product_selection_criteria: [],
            }),
        })
    );
});

test('it returns validation errors when the API rejected the request', async () => {
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
            status: 422,
        }
    );

    const {result} = renderHook(() => useSaveCatalog(), {
        wrapper: ReactQueryWrapper,
    });

    await act(async () => {
        const [success, errors] = await result.current({
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            values: {
                enabled: true,
                product_selection_criteria: [],
            },
        });

        expect(success).toBeFalsy();
        expect(errors).toEqual([
            {
                propertyPath: '[enabled]',
                message: 'Invalid.',
            },
        ]);
    });
});

test('it throws when the API is broken', async () => {
    fetchMock.mockResponseOnce('', {
        status: 500,
    });

    const {result} = renderHook(() => useSaveCatalog(), {
        wrapper: ReactQueryWrapper,
    });

    // mute the error in the output
    jest.spyOn(console, 'error');
    /* eslint-disable-next-line no-console */
    (console.error as jest.Mock).mockImplementation(() => null);

    await expect(async () => {
        await act(async () => {
            await result.current({
                id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
                values: {
                    enabled: true,
                    product_selection_criteria: [],
                },
            });
        });
    }).rejects.toThrow();
});
