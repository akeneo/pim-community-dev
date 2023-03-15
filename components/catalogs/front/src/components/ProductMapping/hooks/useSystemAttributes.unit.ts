import {useSystemAttributes} from './useSystemAttributes';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {renderHook} from '@testing-library/react-hooks';

jest.unmock('./useSystemAttributes');

test('it fetches system attributes', () => {
    const {result} = renderHook(() => useSystemAttributes(), {
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
    ]);
});
