jest.unmock('./useCatalogForm');
jest.unmock('../reducers/CatalogFormReducer');

import {CatalogFormActions} from '../reducers/CatalogFormReducer';
import {act, renderHook} from '@testing-library/react-hooks';
import {mocked} from 'ts-jest/utils';
import {Operator} from '../../ProductSelection';
import {useCatalogForm} from './useCatalogForm';
import {useCatalog} from './useCatalog';
import {SaveCatalog, useSaveCatalog} from './useSaveCatalog';
import {useCatalogErrors} from './useCatalogErrors';

test('it returns a placeholder when loading', () => {
    mocked(useCatalog).mockImplementation(() => ({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    }));
    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    expect(result.current).toMatchObject([undefined, expect.any(Function), false]);
});

test('it throws when the fetch failed', () => {
    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: true,
        data: undefined,
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    expect(() => result.current).toThrow();
});

test('it returns the form values when catalog is loaded', () => {
    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            name: 'Store US',
            enabled: true,
            product_selection_criteria: [
                {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            ],
            product_value_filters: {channels: ['ecommerce', 'print']},
            owner_username: 'willy',
            product_mapping: {},
            has_product_mapping_schema: false,
        },
        error: null,
    }));

    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    expect(result.current).toMatchObject([
        {
            values: {
                enabled: true,
                product_selection_criteria: {
                    a: {
                        field: 'enabled',
                        operator: Operator.EQUALS,
                        value: true,
                    },
                },
                product_value_filters: {channels: ['ecommerce', 'print']},
            },
        },
        expect.any(Function),
        false,
    ]);
});

test('it calls the API when save is called', async () => {
    const saveCatalog = jest.fn(() => Promise.resolve([true, undefined as never])) as SaveCatalog;
    mocked(useSaveCatalog).mockImplementation(() => saveCatalog);

    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            name: 'Store US',
            enabled: true,
            product_selection_criteria: [
                {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            ],
            product_value_filters: {channels: ['ecommerce', 'print']},
            owner_username: 'willy',
            product_mapping: {},
            has_product_mapping_schema: false,
        },
        error: null,
    }));
    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
    const [form, save, isDirty] = result.current;

    await act(async () => {
        const isSaveSuccessful = await save();
        expect(isSaveSuccessful).toBeTruthy();
    });

    expect(saveCatalog).toHaveBeenCalledWith({
        id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
        values: {
            enabled: true,
            product_mapping: {},
            product_selection_criteria: [
                {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            ],
            product_value_filters: {channels: ['ecommerce', 'print']},
        },
    });
});

test('it returns validation errors if the API call failed', async () => {
    const errors = [
        {
            propertyPath: '[enabled]',
            message: 'Invalid',
        },
    ];
    const saveCatalog = jest.fn(() => Promise.resolve([false, errors])) as SaveCatalog;
    mocked(useSaveCatalog).mockImplementation(() => saveCatalog);

    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            name: 'Store US',
            enabled: true,
            product_selection_criteria: [
                {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            ],
            product_value_filters: {channels: ['ecommerce', 'print']},
            owner_username: 'willy',
            product_mapping: {},
            has_product_mapping_schema: false,
        },
        error: null,
    }));

    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
    const [form, save, isDirty] = result.current;

    await act(async () => {
        const isSaveSuccessful = await save();
        expect(isSaveSuccessful).toBeFalsy();
    });

    expect(result.current).toMatchObject([
        {
            errors: errors,
        },
        expect.any(Function),
        false,
    ]);
});

test('it returns dirty at true after dispatching a change', () => {
    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            name: 'Store US',
            enabled: true,
            product_selection_criteria: [
                {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            ],
            product_value_filters: {channels: ['ecommerce', 'print']},
            owner_username: 'willy',
            product_mapping: {},
            has_product_mapping_schema: false,
        },
        error: null,
    }));
    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
    let [form, save, isDirty] = result.current;

    act(() => {
        form && form.dispatch({type: CatalogFormActions.SET_ENABLED, value: true});
    });

    /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
    [form, save, isDirty] = result.current;

    expect(isDirty).toBeTruthy();
});

test("it forwards the action to dispatch when it's a non-altering event", () => {
    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            name: 'Store US',
            enabled: true,
            product_selection_criteria: [
                {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            ],
            product_value_filters: {channels: ['ecommerce', 'print']},
            owner_username: 'willy',
            product_mapping: {},
            has_product_mapping_schema: false,
        },
        error: null,
    }));
    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
    let [form, save, isDirty] = result.current;

    act(() => {
        form &&
            form.dispatch({
                type: CatalogFormActions.INITIALIZE,
                state: {
                    enabled: true,
                    product_selection_criteria: {
                        a: {
                            field: 'enabled',
                            operator: Operator.EQUALS,
                            value: true,
                        },
                    },
                    product_value_filters: {channels: ['ecommerce', 'print']},
                    product_mapping: {},
                },
            });
    });

    /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
    [form, save, isDirty] = result.current;

    expect(isDirty).toBeFalsy();
});

test('it validates the catalog on first load', () => {
    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            name: 'Store US',
            enabled: true,
            product_selection_criteria: [
                {
                    field: 'color',
                    operator: Operator.IN_LIST,
                    value: ['blue', 'red'],
                },
            ],
            product_value_filters: {channels: ['ecommerce', 'print']},
            owner_username: 'willy',
            product_mapping: {},
            has_product_mapping_schema: false,
        },
        error: null,
    }));
    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [
            {
                propertyPath: '[color]',
                message: 'blue does not exists.',
            },
        ],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    expect(result.current).toMatchObject([
        {
            values: {
                enabled: true,
                product_selection_criteria: {
                    a: {
                        field: 'color',
                        operator: Operator.IN_LIST,
                        value: ['blue', 'red'],
                    },
                },
                product_value_filters: {channels: ['ecommerce', 'print']},
                product_mapping: {},
            },
            errors: [
                {
                    propertyPath: '[color]',
                    message: 'blue does not exists.',
                },
            ],
        },
        expect.any(Function),
        false,
    ]);
});

test('it removes the product selection criteria errors when we update a selection criteria', () => {
    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            name: 'Store US',
            enabled: true,
            product_selection_criteria: [
                {
                    field: 'color',
                    operator: Operator.IN_LIST,
                    value: ['blue', 'red'],
                },
            ],
            product_value_filters: {channels: ['ecommerce', 'print']},
            owner_username: 'willy',
            product_mapping: {},
            has_product_mapping_schema: false,
        },
        error: null,
    }));
    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [
            {
                propertyPath: 'productSelectionCriteria[0][color]',
                message: 'Invalid criteria',
            },
            {
                propertyPath: 'productMapping[name][title]',
                message: 'Invalid source value',
            },
        ],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
    const [form, save, isDirty] = result.current;

    act(() => {
        form && form.dispatch({type: CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA, value: {}});
    });

    expect(result.current).toMatchObject([
        {
            values: {
                enabled: true,
                product_selection_criteria: {},
                product_value_filters: {channels: ['ecommerce', 'print']},
                product_mapping: {},
            },
            errors: [
                {
                    propertyPath: 'productMapping[name][title]',
                    message: 'Invalid source value',
                },
            ],
        },
        expect.any(Function),
        true,
    ]);
});

test('it returns the product mapping when catalog is loaded', () => {
    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            name: 'Store US',
            enabled: true,
            product_selection_criteria: [
                {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: true,
                },
            ],
            product_value_filters: {},
            owner_username: 'willy',
            product_mapping: {
                uuid: {
                    source: 'uuid',
                    locale: null,
                    scope: null,
                },
            },
            has_product_mapping_schema: true,
        },
        error: null,
    }));

    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    expect(result.current).toMatchObject([
        {
            values: {
                enabled: true,
                product_selection_criteria: {
                    a: {
                        field: 'enabled',
                        operator: Operator.EQUALS,
                        value: true,
                    },
                },
                product_value_filters: {},
                product_mapping: {
                    uuid: {
                        source: 'uuid',
                        locale: null,
                        scope: null,
                    },
                },
            },
        },
        expect.any(Function),
        false,
    ]);
});

test('it removes the product mapping errors when we update a product mapping source', () => {
    mocked(useCatalog).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            name: 'Store US',
            enabled: true,
            product_selection_criteria: [
                {
                    field: 'color',
                    operator: Operator.IN_LIST,
                    value: ['blue', 'red'],
                },
            ],
            product_value_filters: {},
            owner_username: 'willy',
            product_mapping: {
                uuid: {
                    source: 'uuid',
                    locale: null,
                    scope: null,
                },
                name: {
                    source: 'title',
                    locale: null,
                    scope: null,
                },
            },
            has_product_mapping_schema: true,
        },
        error: null,
    }));
    mocked(useCatalogErrors).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [
            {
                propertyPath: '[product_selection][0][color]',
                message: 'Invalid criteria',
            },
            {
                propertyPath: 'productMapping[name][title]',
                message: 'Invalid source value',
            },
        ],
        error: null,
    }));

    const {result} = renderHook(() => useCatalogForm('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
    const [form] = result.current;

    act(() => {
        form &&
            form.dispatch({
                type: CatalogFormActions.SET_PRODUCT_MAPPING,
                value: {
                    uuid: {
                        source: 'uuid',
                        locale: null,
                        scope: null,
                    },
                    name: {
                        source: 'variation_name',
                        locale: null,
                        scope: null,
                    },
                },
            });
    });

    expect(result.current).toMatchObject([
        {
            values: {
                enabled: true,
                product_selection_criteria: {
                    a: {
                        field: 'color',
                        operator: 'IN',
                        value: ['blue', 'red'],
                    },
                },
                product_value_filters: {},
                product_mapping: {
                    uuid: {
                        source: 'uuid',
                        locale: null,
                        scope: null,
                    },
                    name: {
                        source: 'variation_name',
                        locale: null,
                        scope: null,
                    },
                },
            },
            errors: [
                {
                    propertyPath: '[product_selection][0][color]',
                    message: 'Invalid criteria',
                },
            ],
        },
        expect.any(Function),
        true,
    ]);
});
