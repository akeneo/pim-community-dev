jest.unmock('./useLocales');

import {renderHook} from '@testing-library/react-hooks';
import {useLocales} from './useLocales';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

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

    const {result, waitForNextUpdate} = renderHook(() => useLocales(), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/locales', expect.any(Object));
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
