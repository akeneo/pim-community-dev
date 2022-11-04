jest.unmock('./useTargetsQuery');

import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useTargetsQuery} from './useTargetsQuery';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {code: 'name', label: 'name'},
            {code: 'body_html', label: 'Description'},
        ])
    );

    const {result, waitForNextUpdate} = renderHook(() => useTargetsQuery('123e4567-e89b-12d3-a456-426614174000'), {
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
        '/rest/catalogs/targets/123e4567-e89b-12d3-a456-426614174000',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {code: 'name', label: 'name'},
            {code: 'body_html', label: 'Description'},
        ],
        error: null,
    });
});
