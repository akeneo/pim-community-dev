jest.unmock('./useAttribute');
jest.unmock('../components/ProductMapping/hooks/useSystemAttributes');

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
            attribute_group_code: 'marketing',
            attribute_group_label: 'Marketing',
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
            attribute_group_code: 'marketing',
            attribute_group_label: 'Marketing',
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

test('it returns a system attribute', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useAttribute('categories'), {wrapper: ReactQueryWrapper});

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
        data: {
            code: 'categories',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.categories.label',
            type: 'categories',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        error: null,
    });
});
