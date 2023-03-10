import {useSystemAttribute} from './useSystemAttribute';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {renderHook} from '@testing-library/react-hooks';

jest.unmock('./useSystemAttribute');
jest.unmock('./useSystemAttributes');

test('it fetches categories system attribute', () => {
    const {result} = renderHook(() => useSystemAttribute('categories'), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        code: 'categories',
        label: 'akeneo_catalogs.product_mapping.source.system_attributes.categories.label',
        type: 'categories',
        scopable: false,
        localizable: false,
        attribute_group_code: 'system',
        attribute_group_label: 'System',
    });
});

test('it returns null when the code is no valid', () => {
    const {result} = renderHook(() => useSystemAttribute(''), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toBeNull();
});
