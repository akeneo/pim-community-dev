jest.unmock('./useSystemCriterionFactories');

import {renderHook} from '@testing-library/react-hooks';
import {useSystemCriterionFactories} from './useSystemCriterionFactories';

test('it returns system factories', () => {
    const {result} = renderHook(() => useSystemCriterionFactories());

    expect(result.current).toEqual([
        {
            id: 'enabled',
            label: 'akeneo_catalogs.product_selection.criteria.status.label',
            factory: expect.any(Function),
        },
        {
            id: 'family',
            label: 'akeneo_catalogs.product_selection.criteria.family.label',
            factory: expect.any(Function),
        },
    ]);
});
