import {useSystemAttributesFactories} from './useSystemAttributesFactories';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {renderHook} from '@testing-library/react-hooks';

jest.unmock('./useSystemAttributesFactories');

test('it fetches categories system attribute', () => {
    const {result} = renderHook(() => useSystemAttributesFactories(), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject([
        {
            code: 'categories',
            label: 'akeneo_catalogs.product_mapping.source.system_attributes.categories.label',
            type: 'categories',
            scopable: false,
            localizable: true,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
    ]);
});
