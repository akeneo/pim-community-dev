jest.unmock('./useChannel');

import {renderHook} from '@testing-library/react-hooks';
import {useChannel} from './useChannel';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify({
            code: 'print',
            label: 'Print',
        })
    );

    const {result, waitForNextUpdate} = renderHook(() => useChannel('print'), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels/print', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: {
            code: 'print',
            label: 'Print',
        },
        error: null,
    });
});

test('it returns null when no code provided', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useChannel(null), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: null,
        error: null,
    });
});
