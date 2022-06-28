jest.unmock('./useCatalogData');

import {renderHook} from '@testing-library/react-hooks';
import {useCatalogData} from './useCatalogData';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

test('it fetches catalog data', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify({
            product_selection_criteria: [
                {
                    field: 'enabled',
                    operator: '=',
                    value: true,
                },
            ],
        })
    );

    const id = '123e4567-e89b-12d3-a456-426614174000';
    const {result, waitForNextUpdate} = renderHook(() => useCatalogData(id), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(`/rest/catalogs/${id}/data`, expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: {
            product_selection_criteria: [
                {
                    field: 'enabled',
                    operator: '=',
                    value: true,
                },
            ],
        },
        error: null,
    });
});
