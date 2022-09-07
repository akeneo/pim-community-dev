jest.unmock('./useLocalesByCodes');

import {renderHook} from '@testing-library/react-hooks';

import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

import fetchMock from 'jest-fetch-mock';
import {useLocalesByCodes} from './useLocalesByCodes';

test('it fetches the locales', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                code: 'en_US',
                label: 'English (United States)',
            },
            {
                code: 'fr_FR',
                label: 'French (France)',
            },
        ])
    );

    const {result, waitForNextUpdate} = renderHook(() => useLocalesByCodes(['en_US', 'fr_FR']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/locales?codes=en_US,fr_FR', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'en_US',
                label: 'English (United States)',
            },
            {
                code: 'fr_FR',
                label: 'French (France)',
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
        const {result, waitForNextUpdate} = renderHook(() => useLocalesByCodes(codes), {wrapper: ReactQueryWrapper});

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
