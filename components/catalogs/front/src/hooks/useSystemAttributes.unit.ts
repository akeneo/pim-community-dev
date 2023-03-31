import {useSystemAttributes} from './useSystemAttributes';
import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';
import {renderHook} from '@testing-library/react-hooks';
import {Target} from '../components/ProductMapping/models/Target';

jest.unmock('./useSystemAttributes');

test('it fetches system attributes', () => {
    const {result} = renderHook(() => useSystemAttributes({target: null, search: null}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject([
        {
            code: 'categories',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.categories.label',
            type: 'categories',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'family',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.family.label',
            type: 'family',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'status',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.status.label',
            type: 'status',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
    ]);
});

test('it fetches system attributes for string', () => {
    const target: Target = {
        code: 'category',
        label: 'Category',
        type: 'string',
        format: null,
    };

    const {result} = renderHook(() => useSystemAttributes({target: target, search: null}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject([
        {
            code: 'categories',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.categories.label',
            type: 'categories',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'family',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.family.label',
            type: 'family',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
    ]);
});

test('it fetches system attributes for array of strings', () => {
    const target: Target = {
        code: 'category',
        label: 'Category',
        type: 'array<string>',
        format: null,
    };

    const {result} = renderHook(() => useSystemAttributes({target: target, search: null}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject([
        {
            code: 'categories',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.categories.label',
            type: 'categories',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'family',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.family.label',
            type: 'family',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
    ]);
});

test('it fetches system attributes for boolean', () => {
    const target: Target = {
        code: 'enabled',
        label: 'Enabled',
        type: 'boolean',
        format: null,
    };

    const {result} = renderHook(() => useSystemAttributes({target: target, search: null}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject([
        {
            code: 'status',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.status.label',
            type: 'status',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
    ]);
});

test('it filters system attributes', () => {
    const target: Target = {
        code: 'category',
        label: 'Category',
        type: 'string',
        format: null,
    };

    const {result} = renderHook(() => useSystemAttributes({target: target, search: 'fam'}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject([
        {
            code: 'family',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.family.label',
            type: 'family',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
    ]);
});

test('it fetches system attributes if target is null', () => {
    const {result} = renderHook(() => useSystemAttributes({target: null, search: null}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject([
        {
            code: 'categories',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.categories.label',
            type: 'categories',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'family',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.family.label',
            type: 'family',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'status',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.status.label',
            type: 'status',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
    ]);
});

test('it does not fetches system attributes if target type is not listed', () => {
    const target: Target = {
        code: 'weight',
        label: 'Weight',
        type: 'number',
        format: null,
    };
    const {result} = renderHook(() => useSystemAttributes({target: target, search: null}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject([]);
});

test('it fetches system attributes if target have a format', () => {
    const target: Target = {
        code: 'release_date',
        label: 'Release date',
        type: 'string',
        format: 'date-time',
    };
    const {result} = renderHook(() => useSystemAttributes({target: null, search: null}), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject([
        {
            code: 'categories',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.categories.label',
            type: 'categories',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'family',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.family.label',
            type: 'family',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'status',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.status.label',
            type: 'status',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
    ]);
});
