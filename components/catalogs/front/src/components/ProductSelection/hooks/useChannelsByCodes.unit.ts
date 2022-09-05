jest.unmock('./useChannelsByCodes');

import {renderHook} from '@testing-library/react-hooks';

import fetchMock from 'jest-fetch-mock';
import {useChannelsByCodes} from './useChannelsByCodes';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

test('it fetches the channels', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                code: 'print',
                label: 'Print',
            },
            {
                code: 'ecommerce',
                label: 'Ecommerce',
            },
        ])
    );

    const {result, waitForNextUpdate} = renderHook(() => useChannelsByCodes(['print', 'ecommerce']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels?codes=ecommerce,print', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'print',
                label: 'Print',
            },
            {
                code: 'ecommerce',
                label: 'Ecommerce',
            },
        ],
        error: null,
    });
});

test('it returns an empty array when no code provided', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useChannelsByCodes([]), {wrapper: ReactQueryWrapper});

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
        data: [],
        error: null,
    });
});
