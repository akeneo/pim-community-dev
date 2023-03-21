jest.unmock('./useAssetAttribute');

import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {renderHook} from '@testing-library/react-hooks';
import {useAssetAttribute} from './useAssetAttribute';
import fetchMock from 'jest-fetch-mock';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify({
            identifier: 'attributea_newassetfamily_f80b78d7-43f6-432c-a0a1-fc99ebb4cee7',
            label: 'Attribute A',
            scopable: false,
            localizable: false,
        })
    );

    const {result, waitForNextUpdate} = renderHook(
        () => useAssetAttribute('attributea_newassetfamily_f80b78d7-43f6-432c-a0a1-fc99ebb4cee7'),
        {
            wrapper: ReactQueryWrapper,
        }
    );

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/asset-attributes/attributea_newassetfamily_f80b78d7-43f6-432c-a0a1-fc99ebb4cee7',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: {
            identifier: 'attributea_newassetfamily_f80b78d7-43f6-432c-a0a1-fc99ebb4cee7',
            label: 'Attribute A',
            scopable: false,
            localizable: false,
        },
        error: null,
    });
});

test('it returns undefined when no identifier provided', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useAssetAttribute(''), {wrapper: ReactQueryWrapper});

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
