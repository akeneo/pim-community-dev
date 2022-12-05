import {mockFetchResponses} from '../../tests/mockFetchResponses';

jest.unmock('./useLocalesByCodes');

import {renderHook} from '@testing-library/react-hooks';

import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';

import fetchMock from 'jest-fetch-mock';
import {useLocalesByCodes} from './useLocalesByCodes';

const EN = {code: 'en_US', label: 'English'};
const FR = {code: 'fr_FR', label: 'French'};

beforeEach(() => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/locales?codes=en_US,fr_FR,de_DE',
            json: [EN, FR],
        },
    ]);
});

test('it fetches the locales', async () => {
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
            EN,
            FR,
            {
                code: 'de_DE',
                label: '[de_DE]', // deactivated locales are displayed with brackets
            },
        ],
        error: null,
    });
});

test('it returns no locales when code list is empty', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useLocalesByCodes([]), {
        wrapper: ReactQueryWrapper,
    });

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
