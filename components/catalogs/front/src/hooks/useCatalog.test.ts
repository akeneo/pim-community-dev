jest.unmock('./useCatalog');

import {renderHook} from '@testing-library/react-hooks';
import {useCatalog} from './useCatalog';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                id: '123e4567-e89b-12d3-a456-426614174000',
                name: 'store US',
                enabled: true,
                owner_username: 'willy',
            },
        ])
    );

    const {result, waitForNextUpdate} = renderHook(() => useCatalog('123e4567-e89b-12d3-a456-426614174000'), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/123e4567-e89b-12d3-a456-426614174000', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                id: '123e4567-e89b-12d3-a456-426614174000',
                name: 'store US',
                enabled: true,
                owner_username: 'willy',
            },
        ],
        error: null,
    });
});
