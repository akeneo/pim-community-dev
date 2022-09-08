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

    const {result, waitForNextUpdate} = renderHook(() => useLocalesByCodes(['en_US', 'fr_FR', 'de_DE']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/locales?codes=en_US,fr_FR,de_DE', expect.any(Object));
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
            {
                code: 'de_DE',
                label: '[de_DE]',
            },
        ],
        error: null,
    });
});
