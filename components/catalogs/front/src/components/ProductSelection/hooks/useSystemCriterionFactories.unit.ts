jest.unmock('./useSystemCriterionFactories');

import {renderHook} from '@testing-library/react-hooks';
import {useSystemCriterionFactories} from './useSystemCriterionFactories';

test('it returns system factories', () => {
    const {result} = renderHook(() => useSystemCriterionFactories());

    expect(result.current).toEqual([
        {
            id: 'enabled',
            label: 'akeneo_catalogs.product_selection.criteria.status.label',
            group_code: 'system',
            group_label: 'akeneo_catalogs.product_selection.add_criteria.section_system',
            factory: expect.any(Function),
        },
        {
            id: 'family',
            label: 'akeneo_catalogs.product_selection.criteria.family.label',
            group_code: 'system',
            group_label: 'akeneo_catalogs.product_selection.add_criteria.section_system',
            factory: expect.any(Function),
        },
        {
            id: 'completeness',
            label: 'akeneo_catalogs.product_selection.criteria.completeness.label',
            group_code: 'system',
            group_label: 'akeneo_catalogs.product_selection.add_criteria.section_system',
            factory: expect.any(Function),
        },
        {
            id: 'categories',
            label: 'akeneo_catalogs.product_selection.criteria.category.label',
            group_code: 'system',
            group_label: 'akeneo_catalogs.product_selection.add_criteria.section_system',
            factory: expect.any(Function),
        },
    ]);
});
