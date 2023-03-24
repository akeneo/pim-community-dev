jest.unmock('./useInfiniteAttributeCriterionFactories');

import {act, renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {mocked} from 'ts-jest/utils';
import {Attribute} from '../../../models/Attribute';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useInfiniteAttributeCriterionFactories} from './useInfiniteAttributeCriterionFactories';
import {useFindAttributeCriterionByType} from './useFindAttributeCriterionByType';

const ALLOWED_ATTRIBUTE_TYPES = [
    'pim_catalog_identifier',
    'pim_catalog_text',
    'pim_catalog_textarea',
    'pim_catalog_simpleselect',
    'pim_catalog_multiselect',
    'pim_catalog_number',
    'pim_catalog_metric',
    'pim_catalog_boolean',
    'pim_catalog_date',
];

test('it fetches attributes & paginates criterion factories', async () => {
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
            attribute_group_code: 'technical',
            attribute_group_label: 'Technical',
        },
        {
            code: 'ean',
            label: 'EAN',
            type: 'pim_catalog_text',
            scopable: false,
            localizable: false,
            attribute_group_code: 'other',
            attribute_group_label: '[other]',
        },
    ];

    fetchMock.mockResponseOnce(JSON.stringify(attributes.slice(0, 2)));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteAttributeCriterionFactories({limit: 2}), {
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
                label: 'Name',
                group_code: 'marketing',
                group_label: 'Marketing',
                factory: expect.any(Function),
            },
            {
                label: 'Description',
                factory: expect.any(Function),
                group_code: 'technical',
                group_label: 'Technical',
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
                label: 'Name',
                group_code: 'marketing',
                group_label: 'Marketing',
                factory: expect.any(Function),
            },
            {
                label: 'Description',
                group_code: 'technical',
                group_label: 'Technical',
                factory: expect.any(Function),
            },
            {
                label: 'EAN',
                group_code: 'other',
                group_label: '[other]',
                factory: expect.any(Function),
            },
        ],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it returns a custom factory with the attribute code as field', async () => {
    const factory = jest.fn(state => state);
    const findCriterionByType = jest.fn(() => ({
        factory: factory,
        component: jest.fn(),
    }));
    mocked(useFindAttributeCriterionByType).mockImplementation(() => findCriterionByType);

    const attributes: Attribute[] = [
        {
            code: 'description',
            label: 'Description',
            type: 'pim_catalog_text',
            scopable: false,
            localizable: false,
            attribute_group_code: 'marketing',
            attribute_group_label: 'Marketing',
        },
    ];

    fetchMock.mockResponseOnce(JSON.stringify(attributes));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteAttributeCriterionFactories(), {
        wrapper: ReactQueryWrapper,
    });

    await waitForNextUpdate();

    // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
    expect(result.current.data![0].factory()).toMatchObject({
        field: 'description',
    });
    expect(findCriterionByType).toHaveBeenCalledWith('pim_catalog_text');
    expect(factory).toHaveBeenCalledWith({field: 'description'});
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

    const {result, waitForNextUpdate} = renderHook(
        () => useInfiniteAttributeCriterionFactories({search: 'Description', limit: 2}),
        {
            wrapper: ReactQueryWrapper,
        }
    );

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
                label: 'Description',
                group_code: 'marketing',
                group_label: 'Marketing',
                factory: expect.any(Function),
            },
        ],
        error: null,
        hasNextPage: false,
        fetchNextPage: expect.any(Function),
    });
});

test('it stops fetching if there is no more pages', async () => {
    fetchMock.mockResponseOnce(JSON.stringify([]));

    const {result, waitForNextUpdate} = renderHook(() => useInfiniteAttributeCriterionFactories(), {
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
