import {useInfiniteAttributes} from './useInfiniteAttributes';

jest.unmock('./useInfiniteAttributes');

import {act, renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {Attribute} from '../../../models/Attribute';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

const ALLOWED_ATTRIBUTE_TYPES = ['text', 'textarea'];

test('it fetches attributes & paginates', async () => {
    const attributes: Attribute[] = [
        {
            code: 'name',
            label: 'Name',
            type: 'pim_catalog_text',
            scopable: false,
            localizable: false,
            attribute_group_code: 'marketing',
            attribute_group_label: 'Marketing',
        },
        {
            code: 'description',
            label: 'Description',
            type: 'pim_catalog_text',
            scopable: false,
            localizable: false,
            attribute_group_code: 'marketing',
            attribute_group_label: 'Marketing',
        },
        {
            code: 'ean',
            label: 'EAN',
            type: 'pim_catalog_text',
            scopable: false,
            localizable: false,
            attribute_group_code: 'technical',
            attribute_group_label: 'Technical',
        },
    ];

    fetchMock.mockResponseOnce(JSON.stringify(attributes.slice(0, 2)));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteAttributes({limit: 2}), {
        wrapper: ReactQueryWrapper,
    });

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/attributes?page=1&limit=2&search=&types=' + ALLOWED_ATTRIBUTE_TYPES.join('%2C'),
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'name',
                label: 'Name',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
                attribute_group_code: 'marketing',
                attribute_group_label: 'Marketing',
            },
            {
                code: 'description',
                label: 'Description',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
                attribute_group_code: 'marketing',
                attribute_group_label: 'Marketing',
            },
        ],
        error: null,
        hasNextPage: true,
        fetchNextPage: expect.any(Function),
    });

    fetchMock.mockResponseOnce(JSON.stringify(attributes.slice(2, 3)));

    await act(async () => {
        await result.current.fetchNextPage();
    });

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'name',
                label: 'Name',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
                attribute_group_code: 'marketing',
                attribute_group_label: 'Marketing',
            },
            {
                code: 'description',
                label: 'Description',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
                attribute_group_code: 'marketing',
                attribute_group_label: 'Marketing',
            },
            {
                code: 'ean',
                label: 'EAN',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
                attribute_group_code: 'technical',
                attribute_group_label: 'Technical',
            },
        ],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it searches with a string', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                code: 'description',
                label: 'Description',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
                attribute_group_code: 'marketing',
                attribute_group_label: 'Marketing',
            },
        ])
    );

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteAttributes({search: 'Description', limit: 2}), {
        wrapper: ReactQueryWrapper,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/attributes?page=1&limit=2&search=Description&types=' + ALLOWED_ATTRIBUTE_TYPES.join('%2C'),
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'description',
                label: 'Description',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
                attribute_group_code: 'marketing',
                attribute_group_label: 'Marketing',
            },
        ],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it stops fetching if there is no more pages', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteAttributes(), {
        wrapper: ReactQueryWrapper,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledTimes(1);
    expect(result.current).toMatchObject({
        hasNextPage: false,
    });

    await result.current.fetchNextPage();

    expect(fetchMock).toHaveBeenCalledTimes(1);
});
