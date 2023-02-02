jest.unmock('./useChannelCurrencies');

import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useChannelCurrencies} from './useChannelCurrencies';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(JSON.stringify(['USD', 'EUR']));

    const {result, waitForNextUpdate} = renderHook(() => useChannelCurrencies('print'), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels/print/currencies', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: ['USD', 'EUR'],
        error: null,
    });
});
