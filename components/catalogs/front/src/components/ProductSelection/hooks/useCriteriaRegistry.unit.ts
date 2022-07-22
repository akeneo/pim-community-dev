jest.unmock('./useCriteriaRegistry');

import {renderHook} from '@testing-library/react-hooks';
import {useCriteriaRegistry} from './useCriteriaRegistry';

test('it returns factories & a function to find a criterion', () => {
    const {result} = renderHook(() => useCriteriaRegistry());

    expect(result.current).toEqual({
        system: [
            {
                label: 'akeneo_catalogs.product_selection.criteria.status.label',
                factory: expect.any(Function),
            },
            {
                label: 'akeneo_catalogs.product_selection.criteria.family.label',
                factory: expect.any(Function),
            },
            {
                label: 'akeneo_catalogs.product_selection.criteria.completeness.label',
                factory: expect.any(Function),
            },
        ],
        getCriterionByField: expect.any(Function),
    });
});

test('it throws when searching for an unknown field', async () => {
    const {result} = renderHook(() => useCriteriaRegistry());

    await expect(async () => await result.current.getCriterionByField('foo')).rejects.not.toBeNull();
});

const criterions: string[] = ['enabled', 'family', 'completeness'];

test.each(criterions)('it returns a criterion when searching for "%s"', async field => {
    const {result} = renderHook(() => useCriteriaRegistry());

    expect(await result.current.getCriterionByField(field)).toMatchObject({
        component: expect.any(Function),
        factory: expect.any(Function),
    });
});
