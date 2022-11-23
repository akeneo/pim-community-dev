import {useChannel} from '../components/ProductSelection/hooks/useChannel';

jest.unmock('./useAttribute');

import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';
import {renderHook} from '@testing-library/react-hooks';
import {useAttribute} from './useAttribute';
import fetchMock from 'jest-fetch-mock';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify({
            code: 'name',
            label: 'Name',
            type: 'pim_catalog_text',
            scopable: false,
            localizable: false,
        })
    );

    const {result, waitForNextUpdate} = renderHook(() => useAttribute('name'), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith('/rest/catalogs/attributes/name', expect.any(Object));
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: {
            code: 'name',
            label: 'Name',
            type: 'pim_catalog_text',
            scopable: false,
            localizable: false,
        },
        error: null,
    });
});

test('it returns undefined when no code provided', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useAttribute(''), {wrapper: ReactQueryWrapper});

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
        data: undefined,
        error: null,
    });
});
