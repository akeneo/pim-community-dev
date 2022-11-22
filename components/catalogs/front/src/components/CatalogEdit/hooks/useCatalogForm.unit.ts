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

test('it remove all errors when we delete a product selection criteria', () => {
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
            },
            errors: [],
        },
        expect.any(Function),
        true,
    ]);
});
