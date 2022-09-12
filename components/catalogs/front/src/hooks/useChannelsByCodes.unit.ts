jest.unmock('./useChannelsByCodes');

import {renderHook} from '@testing-library/react-hooks';

import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';

import fetchMock from 'jest-fetch-mock';
import {useChannelsByCodes} from './useChannelsByCodes';

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

    const {result, waitForNextUpdate} = renderHook(() => useChannelsByCodes(['print', 'ecommerce', 'mobile']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/channels?codes=print,ecommerce,mobile', expect.any(Object));
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
            {
                code: 'mobile',
                label: '[mobile]',
            },
        ],
        error: null,
    });
});
