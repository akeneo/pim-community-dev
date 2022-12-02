jest.unmock('./useChannelLocales');

import {renderHook} from '@testing-library/react-hooks';
import {useChannelLocales} from './useChannelLocales';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                code: 'de_DE',
                label: 'German (Germany)',
            },
            {
                code: 'en_US',
                label: 'English (United States)',
            },
        ])
    );

    const {result, waitForNextUpdate} = renderHook(() => useChannelLocales('print'), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels/print/locales', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'de_DE',
                label: 'German (Germany)',
            },
            {
                code: 'en_US',
                label: 'English (United States)',
            },
        ],
        error: null,
    });
});

test('it returns an empty array when no code provided', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useChannelLocales(null), {wrapper: ReactQueryWrapper});

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
