import {renderHook} from '@testing-library/react-hooks';

import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';

import fetchMock from 'jest-fetch-mock';
import {useChannelsByCodes} from './useChannelsByCodes';

jest.unmock('./useChannelsByCodes');

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

const codesTests: [string, string[] | undefined][] = [
    ['undefined', undefined],
    ['empty array', []],
];

test.each(codesTests)(
    'it returns an empty array when "%s" as codes is provided',
    async (key: string, codes: string[] | undefined) => {
        const {result, waitForNextUpdate} = renderHook(() => useChannelsByCodes(codes), {wrapper: ReactQueryWrapper});

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
    }
);
